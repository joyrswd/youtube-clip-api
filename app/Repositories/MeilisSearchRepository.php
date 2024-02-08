<?php

namespace App\Repositories;

use MeiliSearch\Client;
use Meilisearch\Endpoints\Indexes;

class MeilisSearchRepository
{

    private Client $client;

    private Indexes $index;

    public function __construct(string $modelName)
    {
        $this->client = new Client(env('MEILI_HTTP_ADDR'), env('MEILI_MASTER_KEY'));
        $indexName = (new $modelName)->searchableAs();
        $this->index = $this->client->index($indexName);
    }

    public function deleteIndex (): void
    {        
        $this->index->delete();
    }

    public function truncate (): void
    {
        $this->index->deleteAllDocuments();
    }

}
