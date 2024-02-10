<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\YoutubeService;
use App\Services\ChannelService;
use App\Services\VideoService;
use Carbon\Carbon;

class UpdateChannelBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:update-channel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    private YoutubeService $youtubeService;
    private ChannelService $channelService;
    private VideoService $videoService;

    public function __construct(YoutubeService $youtubeService, ChannelService $channelService, VideoService $videoService)
    {
        parent::__construct();
        $this->youtubeService = $youtubeService;
        $this->channelService = $channelService;
        $this->videoService = $videoService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 全てのチャンネルを取得
        $channels = $this->channelService->getAllChannels();
        foreach ($channels as $channel) {
            if ($this->isUpdateTiming($channel['new_stocked_at']) === false) {
                continue;
            }
            $this->info("『{$channel['title']}』の動画情報を更新します。");
            $nextToken = null;
            $itemCount = 0;
            do {
                [$ids, $token] = $this->youtubeService->findVideoIds($channel['youtube_id'], ['publishedAfter' => $channel['new_stocked_at']], $nextToken);
                if (empty($ids)) break;
                $videos = $this->youtubeService->findVideoInfoByIds($ids, $channel['id']);
                foreach ($videos as $video) {
                    $this->videoService->upsert($video);
                    $itemCount++;
                }
            } while ($nextToken = $token);
            $this->info($itemCount . '件の動画情報を保存しました。');
        }
        return true;
    }

    /** 
     * 最新更新時刻+1時間（〇時）と現在時刻（〇時）が一致するかどうか
     */
    private function isUpdateTiming($newStockedAt)
    {
        $currentHour = (new Carbon())->format('H');
        $targetHour = Carbon::parse($newStockedAt)->format('H');
        return ($currentHour == $targetHour + 1);
    }
}
