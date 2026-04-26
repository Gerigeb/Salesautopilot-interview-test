<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\View\View;

class ListController extends Controller
{
    public function __construct(
        private readonly ApiClient $client,
    ) {}

    //I didn't find any endpoint that returns the lists' created date, so I didn't include it on the listing page.
    public function index(): View
    {
        try {
            $lists = $this->client->get('/lists')['data'];
        } catch (RequestException $e) {
            return view('lists', ['error' => $e->response->json('message') ?? 'Failed to load lists. Please try again later.']);
        } catch (ConnectionException) {
            return view('lists', ['error' => 'Could not connect to the service. Please try again later.']);
        }

        $counts = [];
        foreach ($lists as $list) {
            try {
                $counts[$list['listId']] = $this->fetchSubscriberCount($list['listId']);
            } catch (RequestException $e) {
                return view('lists', ['error' => $e->response->json('message') ?? 'Failed to load subscriber counts. Please try again later.']);
            } catch (ConnectionException) {
                return view('lists', ['error' => 'Could not connect to the service. Please try again later.']);
            }
        }

        return view('lists', ['lists' => $lists, 'counts' => $counts]);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    private function fetchSubscriberCount(int $listId): string
    {
        $count = 0;
        $page = 1;
        $limit = 25;
        $maxPages = 2;
        $lastPageCount = $limit;

        do {
            $result = $this->client->get("/newsletter/{$listId}/subscribers", ['limit' => $limit, 'page' => $page]);
            $lastPageCount = count($result['subscribers'] ?? []);
            $count += $lastPageCount;
            $page++;
        } while ($lastPageCount === $limit && $page <= $maxPages);

        return ($page > $maxPages && $lastPageCount === $limit) ? "{$count}+" : (string) $count;
    }

    public function show(int $id): View
    {
        try {
            $subscribers = $this->client->get("/newsletter/{$id}/subscribers", ['limit' => 20]);
        } catch (RequestException $e) {
            return view('list-details', ['id' => $id, 'error' => $e->response->json('message') ?? 'Failed to load subscribers. Please try again later.']);
        } catch (ConnectionException) {
            return view('list-details', ['id' => $id, 'error' => 'Could not connect to the service. Please try again later.']);
        }

        return view('list-details', ['id' => $id, 'subscribers' => $subscribers['subscribers']]);
    }
}
