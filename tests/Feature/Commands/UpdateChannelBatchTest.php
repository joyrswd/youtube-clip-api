<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\YoutubeService;
use App\Models\Channel;
use App\Models\Video;
use Mockery;
use Mockery\Mock;
use Carbon\Carbon;

class UpdateChannelBatchTest extends TestCase
{
    use RefreshDatabase;

    private YoutubeService|Mock $youtubeService;
    private Channel $channel;
    private Video $video;

    public function setUp(): void
    {
        parent::setUp();
        // Channelを作成
        $this->channel = Channel::factory()->create([
            'youtube_id' => 'channel_id',
        ]);
        // 動画情報を作成
        $this->video = Video::factory()->create([
            'channel_id' => $this->channel->id,
            'youtube_id' => 'video_id1',
            'title' => 'video_title1',
            'description' => 'video_description1',
            'duration' => 60 * 10,
            'published_at' => '2021-01-31 15:00:00',
        ]);

        // YoutubeServiceのモックを作成
        $youtubeService = Mockery::mock(YoutubeService::class);
        // findVideoIdsをモック
        $youtubeService->shouldReceive('findVideoIds')
            ->andReturnUsing(function($channleId, $params){
                if ($params['publishedAfter'] < '2021-02-02 00:00:00') {
                    return [['video_id2'], null];
                } else {
                    return [[], null];
                }
            });
        $youtubeService->shouldReceive('findVideoInfoByIds')
            ->andReturnUsing(function($ids, $channelId){
                return [
                    [
                        'channel_id' => $this->channel->id,
                        'youtube_id' => 'video_id2',
                        'etag' => 'etag2',
                        'title' => 'video_title2',
                        'description' => 'video_description2',
                        'duration' => 'PT5M', // 5分
                        'published_at' => '2021-02-02 00:00:00',
                    ]
                ];
            });
        $this->app->instance(YoutubeService::class, $youtubeService);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }


    /**
    * @test
    */
    public function handle_正常系()
    {
        // システム時刻を固定
        Carbon::setTestNow(new Carbon('2021-02-02 01:00:00'));
        $this->artisan('batch:update-channel')
            ->assertExitCode(1);
        // 新しい動画情報が追加されたことを確認
        $this->assertDatabaseHas('videos', [
            'channel_id' => $this->channel->id,
            'youtube_id' => 'video_id2',
            'title' => 'video_title2',
            'description' => 'video_description2',
            'duration' => 60 * 5,
            'published_at' => '2021-02-02 00:00:00',
        ]);
    }

    /**
     * @test
     */
    public function handle_更新対象外()
    {
        // システム時刻を固定
        Carbon::setTestNow(new Carbon('2021-02-02 00:00:00'));
        $this->artisan('batch:update-channel')
            ->assertExitCode(1);
        // 新しい動画情報が追加されていないことを確認
        $this->assertDatabaseMissing('videos', [
            'channel_id' => $this->channel->id,
            'youtube_id' => 'video_id2',
            'title' => 'video_title2',
            'description' => 'video_description2',
            'duration' => 60 * 5,
            'published_at' => '2021-02-02 00:00:00',
        ]);
    }
}