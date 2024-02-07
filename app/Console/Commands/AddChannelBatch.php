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
        $total = $this->saveAllVideos($channelId);
        $this->info($total . '件の動画情報を保存しました。');
        return true;
    }

    private function setUpChannelInfo() : ?string
    {
        if(($channelId = $this->askChannelId())
            && ($channelName = $this->findChannelInfo($channelId))
            && $this->confirm('『' . $channelName . '』を登録します。よろしいですか？')) {
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
        //既存のチャンネルか確認
        if ($row = $this->channelService->findByYoutubeId($id)) {
            $this->error("『{$row['title']}』は既に登録されています");
            return null;
        }
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
            [$ids, $token] = $this->youtubeService->findVideoIds($channel['youtube_id'], $nextToken);
            $items = $this->fetchVideoData($ids, $channel['id']);
            $itemCount += count($items);
            $this->info('取得件数: ' . $itemCount);
        } while ($nextToken = $token);
        return $itemCount;
    }

    private function fetchVideoData(array $ids, int $channelId)
    {
        $items = [];
        $videos = $this->youtubeService->findVideInfoByIds($ids);
        foreach ($videos as $video) {
            $item = $this->youtubeService->convertToVideoRecord($video, $channelId);
            $videId = $this->videoService->create($item);
            if (empty($item['tags']) === false) {
                $this->tagService->addTags($item['tags'], $videId);
            }
            $items[] = $videId;
        }
        return $items;
    }

}
