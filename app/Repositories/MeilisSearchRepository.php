<?php

namespace App\Repositories;

use MeiliSearch\Client;
use Meilisearch\Endpoints\Indexes;

class MeilisSearchRepository
{

    private Client $client;

    private ?Indexes $index;

    private string $indexName;

    public function __construct(string $modelName)
    {
        $this->indexName = (new $modelName)->searchableAs();
        $this->client = new Client(env('MEILI_HTTP_ADDR'), env('MEILI_MASTER_KEY'));
        $this->index = $this->client->index($this->indexName);
    }

    public function getIndexName (): string
    {
        return $this->indexName;
    }

    public function getIndexInfo (): ?Array
    {
        try {
            return $this->index->stats();
        } catch (\Exception $e) {
            return null;
        }
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
