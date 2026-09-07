<?php

declare(strict_types=1);

namespace Mitoera\Sdk\Client;

use Mitoera\Sdk\MitoeraClient;
use Mitoera\Sdk\Response\WorkspaceResponse;

/** @internal Accessed via $client->workspaces */
class WorkspacesClient
{
    public function __construct(private readonly MitoeraClient $client) {}

    /** @return WorkspaceResponse[] */
    public function listAll(): array
    {
        $data = $this->client->get($this->client->apiPrefix . '/workspaces');
        return array_map(WorkspaceResponse::fromArray(...), $data['items'] ?? $data);
    }

    public function getCurrent(): WorkspaceResponse
    {
        return WorkspaceResponse::fromArray(
            $this->client->get($this->client->apiPrefix . '/workspaces/current')
        );
    }

    public function create(string $name): WorkspaceResponse
    {
        return WorkspaceResponse::fromArray(
            $this->client->post($this->client->apiPrefix . '/workspaces', ['name' => $name])
        );
    }

    public function switchTo(string $workspaceId): WorkspaceResponse
    {
        return WorkspaceResponse::fromArray(
            $this->client->post(
                $this->client->apiPrefix . '/workspaces/' . $workspaceId . '/switch'
            )
        );
    }

    public function invite(string $email, string $role = 'MEMBER'): void
    {
        $this->client->post(
            $this->client->apiPrefix . '/workspaces/invite',
            ['email' => $email, 'role' => $role],
        );
    }

    /** @return array<array{id:string, email:string, role:string}> */
    public function listMembers(): array
    {
        $data = $this->client->get($this->client->apiPrefix . '/workspaces/members');
        return $data['items'] ?? $data;
    }
}
