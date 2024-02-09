<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Channel;
use App\Models\Video;
use App\Services\YoutubeService;
use App\Repositories\MeilisSearchRepository;
use Mockery;
use Mockery\Mock;

class AddChannelBatchTest extends TestCase
{
    use RefreshDatabase;

    private YoutubeService|Mock $youtubeService;

    public function setUp(): void
    {
        parent::setUp();
        // YoutubeServiceのモックを作成
        $youtubeService = Mockery::mock(YoutubeService::class);
        // getChannelTitleByIdメソッドをモック
        $youtubeService->shouldReceive('getChannelTitleById')
            ->andReturnUsing(function ($channelId) {
                return ($channelId === 'channel_id') ? 'channel_title' : null;
            });
        // findVideoIdsをモック
        $youtubeService->shouldReceive('findVideoIds')
            ->with('channel_id', null)
            ->andReturn([['video_id1', 'video_id2'], null]);
        // findVideoInfoByIdsをモック
        $youtubeService->shouldReceive('findVideoInfoByIds')
        ->andReturnUsing(function ($videoIds, $channelId) {
            return [
                [
                    'channel_id' => $channelId,
                    'etag' => 'etag1',
                    'youtube_id' => $videoIds[0],
                    'title' => 'video_title1',
                    'description' => 'video_description1',
                    'duration' => 'PT1H1M1S',
                    'published_at' => new \DateTime('2021-01-01 00:00:00'),
                ],
                [
                    'channel_id' => $channelId,
                    'etag' => 'etag2',
                    'youtube_id' => $videoIds[1],
                    'title' => 'video_title2',
                    'description' => 'video_description2',
                    'duration' => 'PT2H2M2S',
                    'published_at' => new \DateTime('2021-02-02 00:00:00'),
                    'tags' => ['tag1', 'tag2'],
                ],
            ];
        });
        $this->app->instance(YoutubeService::class, $youtubeService);
    }

    public function tearDown(): void
    {
        (new MeilisSearchRepository(Video::class))->deleteIndex();
        parent::tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function handle_正常系()
    {
        $this->artisan('batch:add-channel')
            ->expectsOutput('Start batch:add-channel')
            ->expectsQuestion('チャンネルIDを入力してください', 'channel_id')
            ->expectsQuestion('『channel_title』を登録しますか？', true)
            ->assertExitCode(1);
        $this->assertDatabaseHas('channels', ['youtube_id' => 'channel_id', 'title' => 'channel_title']);
        $this->assertDatabaseHas('videos', ['youtube_id' => 'video_id1', 'title' => 'video_title1']);
        $this->assertDatabaseHas('videos', ['youtube_id' => 'video_id2', 'title' => 'video_title2']);
        $this->assertDatabaseHas('tags', ['name' => 'tag1']);
        $this->assertDatabaseHas('tags', ['name' => 'tag2']);
        $this->assertDatabaseHas('tag_video', ['video_id' => 2, 'tag_id' => 1]);
        $this->assertDatabaseHas('tag_video', ['video_id' => 2, 'tag_id' => 2]);
        $this->assertDatabaseMissing('tag_video', ['video_id' => 1, 'tag_id' => 1]);
        $this->assertDatabaseMissing('tag_video', ['video_id' => 1, 'tag_id' => 2]);
    }

    /**
     * @test
     */
    public function handle_登録済みチャンネル再登録()
    {
        $channel = Channel::factory()->create(['youtube_id' => 'channel_id', 'title' => 'channel_title']);
        Video::factory()->create(['channel_id' => $channel->id, 'youtube_id' => 'video_id1', 'title' => 'video_title_exist']);
        $this->artisan('batch:add-channel')
            ->expectsOutput('Start batch:add-channel')
            ->expectsQuestion('チャンネルIDを入力してください', 'channel_id')
            ->expectsQuestion('『channel_title』は登録されています。再登録しますか？', true)
            ->assertExitCode(1)
            ;
        $this->assertDatabaseHas('channels', ['youtube_id' => 'channel_id', 'title' => 'channel_title']);
        $this->assertDatabaseHas('videos', ['youtube_id' => 'video_id1', 'title' => 'video_title1']);
        $this->assertDatabaseHas('videos', ['youtube_id' => 'video_id2', 'title' => 'video_title2']);
    }

    /**
     * @test
     */
    public function handle_ID入力後キャンセル()
    {
        $this->artisan('batch:add-channel')
            ->expectsOutput('Start batch:add-channel')
            ->expectsQuestion('チャンネルIDを入力してください', 'channel_id')
            ->expectsQuestion('『channel_title』を登録しますか？', false)
            ->expectsOutput('処理はキャンセルされました。')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function handle_不正なID入力()
    {
        $this->artisan('batch:add-channel')
            ->expectsOutput('Start batch:add-channel')
            ->expectsQuestion('チャンネルIDを入力してください', '')
            ->expectsOutput('処理はキャンセルされました。')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function handle_登録済みチャンネル再登録_キャンセル()
    {
        Channel::factory()->create(['youtube_id' => 'channel_id_exist', 'title' => 'channel_title']);
        $this->artisan('batch:add-channel')
            ->expectsOutput('Start batch:add-channel')
            ->expectsQuestion('チャンネルIDを入力してください', 'channel_id_exist')
            ->expectsQuestion('『channel_title』は登録されています。再登録しますか？', false)
            ->expectsOutput('処理はキャンセルされました。')
            ->assertExitCode(0);
    }

    /**
     * @test
     */
    public function handle_チャンネルが見つからない()
    {
        $this->artisan('batch:add-channel')
            ->expectsOutput('Start batch:add-channel')
            ->expectsQuestion('チャンネルIDを入力してください', 'not_found_channel_id')
            ->expectsOutput('チャンネルが見つかりません')
            ->assertExitCode(0);
    }

}
