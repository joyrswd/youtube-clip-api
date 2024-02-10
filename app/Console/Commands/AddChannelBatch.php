<?php

namespace App\Console\Commands;

use App\Services\ChannelService;
use App\Services\VideoService;
use App\Services\TagService;
use App\Services\YoutubeService;
use Illuminate\Console\Command;

class AddChannelBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:add-channel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '新しいYoutubeチャンネルを登録し、動画情報を全保存するバッチ';

    private YoutubeService $youtubeService;

    private ChannelService $channelService;

    private VideoService $videoService;

    private TagService $tagService;

    public function __construct(ChannelService $channelService, VideoService $videoService, TagService $tagService, YoutubeService $youtubeService)
    {
        parent::__construct();
        try {
            $this->channelService = $channelService;
            $this->videoService = $videoService;
            $this->tagService = $tagService;
            $this->youtubeService = $youtubeService;
        } catch (\Exception $e) {
            $this->error('Youtube API Error');
        }
    }

    /**
     * Execute the console command.
     */
    public function handle() : bool
    {
        //起動開始を出力
        $this->info('Start ' . $this->signature);
        $channelId = $this->setUpChannelInfo();
        if (empty($channelId)) {
            $this->info('処理はキャンセルされました。');
            return false;
        }
        try {
            $total = $this->saveAllVideos($channelId);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return false;
        }
        $this->info($total . '件の動画情報を保存しました。');
        return true;
    }

    private function setUpChannelInfo() : ?string
    {
        $channelId = $this->askChannelId();
        if (empty($channelId)) {
            return null;
        }
        if (($channel = $this->channelService->findByYoutubeId($channelId))) {
            return $this->confirm('『' . $channel['title'] . '』は登録されています。再登録しますか？') ? $channelId : null;
        } elseif (($channelName = $this->findChannelInfo($channelId))
            && $this->confirm('『' . $channelName . '』を登録しますか？')) {
            $this->channelService->create($channelId, $channelName);
            return $channelId;
        } else {
            return null;
        }
    }

    private function askChannelId(): ?string
    {
        $channelId = $this->ask('チャンネルIDを入力してください');
        if (empty($channelId)) {
            $this->error('チャンネルIDが入力されていません');
            return null;
        }
        return $channelId;
    }

    private function findChannelInfo(string $id): ?string
    {
        //チャンネル情報を取得する処理
        $channelTitle = $this->youtubeService->getChannelTitleById($id);
        if (empty($channelTitle)) {
            $this->error('チャンネルが見つかりません');
            return null;
        }
        return $channelTitle;
    }

    private function saveAllVideos(string $channelId): int
    {
        $channel = $this->channelService->findByYoutubeId($channelId);
        $nextToken = null;
        $itemCount = 0;
        do {
            [$ids, $token] = $this->youtubeService->findVideoIds($channel['youtube_id'], [], $nextToken);
            if (empty($ids)) {
                break;
            }
            $videos = $this->youtubeService->findVideoInfoByIds($ids, $channel['id']);
            $items = $this->fetchVideoData($videos);
            $itemCount += count($items);
            $this->info('取得件数: ' . $itemCount);
        } while ($nextToken = $token);
        return $itemCount;
    }

    private function fetchVideoData(array $videos)
    {
        $items = [];
        foreach ($videos as $video) {
            $videoId = $this->videoService->upsert($video);
            if (empty($video['tags']) === false) {
                $this->tagService->addTags($video['tags'], $videoId);
            }
            $items[] = $videoId;
        }
        return $items;
    }

}
