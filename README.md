# Mitoera PHP SDK

SDK officiel Mitoera pour PHP. Utilisé côté serveur pour gérer les holds, réservations, événements, plans de salle et workspaces.

## Installation

```bash
composer require mitoera/php-sdk
```

**Prérequis :** PHP ≥ 8.1, Guzzle 7

## Démarrage rapide

```php
use Mitoera\Sdk\MitoeraClient;

$client = new MitoeraClient([
    'keyId'  => 'pk_live_xxxx',  // visible dans BO > Clés API
    'secret' => 'sk_xxxxxxxx',
]);

// Bloquer des sièges
$hold = $client->holds->hold($eventId, ['A1', 'A2'], $holdToken);

// Confirmer la réservation après paiement
$book = $client->holds->book($eventId, ['A1', 'A2'], $holdToken);

// Libérer si l'utilisateur abandonne
$client->holds->release($eventId, ['A1', 'A2'], $holdToken);
```

## Clés API

| Format | Environnement |
|--------|--------------|
| `pk_live_xxxx` | Production |
| `pk_test_xxxx` | Sandbox (auto-détecté) |

Sandbox et production partagent le **même endpoint `/api/`**. L'environnement est déduit côté serveur depuis le préfixe de la clé (`pk_test_` → instance sandbox, `pk_live_` → instance production).

Options disponibles du constructeur :

| Option | Type | Défaut | Description |
|--------|------|--------|-------------|
| `keyId` | string | — | **Requis.** Clé publique |
| `secret` | string | — | **Requis.** Clé secrète |
| `baseUrl` | string | `https://api.mitoera.com` | URL de l'API |
| `timeout` | int | `30` | Timeout HTTP en secondes |

---

## Clients disponibles

### `$client->holds` — Gestion des sièges

```php
// Bloquer des sièges (10 min par défaut)
$hold = $client->holds->hold(string $eventId, array $seatKeys, string $holdToken): HoldResponse

// Confirmer la réservation
$book = $client->holds->book(string $eventId, array $seatKeys, string $holdToken): BookResponse

// Libérer des sièges
$client->holds->release(string $eventId, array $seatKeys, string $holdToken): void

// Changer le statut manuellement (ex: 'available', 'held', 'booked')
$client->holds->changeStatus(string $eventId, array $seatKeys, string $status): void
```

**`HoldResponse`**

| Propriété | Type | Description |
|-----------|------|-------------|
| `holdToken` | string | Token du hold |
| `seatKeys` | string[] | Sièges bloqués |
| `expiresAt` | DateTimeImmutable | Expiration |
| `durationSeconds` | int | Durée du hold |

**`BookResponse`**

| Propriété | Type | Description |
|-----------|------|-------------|
| `bookedSeats` | string[] | Sièges réservés |
| `eventId` | string | UUID de l'événement |
| `bookedAt` | DateTimeImmutable | Horodatage |

---

### `$client->sessions` — Sessions widget

Crée des sessions pour autoriser le widget front-end à interagir avec un événement.

```php
// Nouvelle session (à appeler depuis votre back-end avant d'afficher le widget)
$session = $client->sessions->create(string $eventId): SessionResponse

// Rafraîchir une session expirée
$session = $client->sessions->refresh(string $sessionToken): SessionResponse
```

**`SessionResponse`**

| Propriété | Type | Description |
|-----------|------|-------------|
| `sessionToken` | string | Passer au widget JS |
| `holdToken` | string | Token de hold de la session |
| `eventId` | string | UUID de l'événement |
| `expiresIn` | int | Durée en secondes |

---

### `$client->events` — Événements

```php
$client->events->listAll(): EventResponse[]
$client->events->get(string $eventId): EventResponse
$client->events->findByIdentifier(string $identifier): EventResponse
$client->events->listSeats(string $eventId, ?array $seatKeys = null): SeatStatusMap
$client->events->bulkUpdateSeats(string $eventId, array $seatKeys, string $status): int
$client->events->create(string $title, string $identifier, ?string $chartId = null): EventResponse
$client->events->update(string $eventId, array $fields): EventResponse
$client->events->linkChart(string $eventId, string $chartId): void
$client->events->delete(string $eventId): void
```

**`SeatStatusMap`** — Résultat de `listSeats()`

```php
$map = $client->events->listSeats($eventId);

$map->available();           // string[] — sièges libres
$map->held();                // string[] — sièges bloqués
$map->booked();              // string[] — sièges réservés
$map->isAvailable('A1');     // bool
$map->byStatus('held');      // string[]
$map->get('A1');             // 'available'|'held'|'booked'|null

foreach ($map as $seatKey => $status) { ... }
```

---

### `$client->charts` — Plans de salle

```php
$client->charts->listAll(): ChartResponse[]
$client->charts->get(string $chartId): ChartResponse
$client->charts->create(string $name): ChartResponse
$client->charts->update(string $chartId, array $fields): ChartResponse
$client->charts->setObjects(string $chartId, array $objects): ChartResponse
$client->charts->publish(string $chartId): ChartResponse
$client->charts->markPending(string $chartId): ChartResponse
$client->charts->delete(string $chartId): void
```

**`ChartResponse`**

| Propriété | Type | Description |
|-----------|------|-------------|
| `id` | string | UUID |
| `name` | string | Nom du plan |
| `objects` | array | Objets du plan (sièges, formes…) |
| `status` | string | `'draft'` ou `'published'` |
| `pendingChanges` | bool | Modifications non publiées |
| `updatedAt` | DateTimeImmutable | Dernière modification |
| `publishedSnapshot` | array\|null | Snapshot publié |

```php
$chart->isPublished(); // bool
$chart->isDraft();     // bool
```

---

### `$client->categories` — Catégories de plan

```php
$client->categories->listForChart(string $chartId): CategoryResponse[]
$client->categories->get(string $chartId, int $categoryKey): CategoryResponse
$client->categories->create(string $chartId, string $name, string $color): CategoryResponse
$client->categories->update(string $chartId, int $categoryKey, array $fields): CategoryResponse
$client->categories->delete(string $chartId, int $categoryKey): void
```

---

### `$client->workspaces` — Workspaces

```php
$client->workspaces->listAll(): WorkspaceResponse[]
$client->workspaces->getCurrent(): WorkspaceResponse
$client->workspaces->create(string $name): WorkspaceResponse
$client->workspaces->switchTo(string $workspaceId): WorkspaceResponse
$client->workspaces->invite(string $email, string $role = 'MEMBER'): void
$client->workspaces->listMembers(): array
```

---

### `$client->apiKeys` — Clés API

```php
$client->apiKeys->listAll(): ApiKeyResponse[]
$client->apiKeys->create(string $name, string $scope = 'PUBLIC'): ApiKeyCreatedResponse
$client->apiKeys->delete(string $apiKeyId): void
```

> **Note :** `ApiKeyCreatedResponse` contient le champ `secret` (la clé secrète), affiché **une seule fois** à la création. Conservez-le immédiatement.

---

## Gestion des erreurs

```php
use Mitoera\Sdk\Exception\ApiException;
use Mitoera\Sdk\Exception\AuthException;

try {
    $client->holds->hold($eventId, ['A1'], $holdToken);
} catch (AuthException $e) {
    // 401/403, ou keyId/secret manquant — à attraper AVANT ApiException
    echo $e->statusCode;   // 401  (0 si l'erreur est une erreur de configuration)
    echo $e->getMessage(); // message nommant les causes probables
} catch (ApiException $e) {
    // Toute autre erreur HTTP
    echo $e->statusCode;   // 409
    echo $e->getMessage(); // "Seat already held"
    var_dump($e->body);    // corps JSON décodé
}
```

| Exception | Cause | `statusCode` |
|-----------|-------|--------------|
| `AuthException` | `keyId`/`secret` manquant, ou 401/403 renvoyé par l'API | `0` si erreur de configuration, sinon `401`/`403` |
| `ApiException` | Toute autre erreur HTTP (4xx, 5xx) | le code renvoyé |
| `MitoeraException` | Échec de transport (cURL) | — |

`AuthException` **hérite de** `ApiException`, qui hérite de `MitoeraException`. Un
`catch (ApiException $e)` attrape donc aussi les échecs d'authentification : placez le bloc
`AuthException` en premier si vous voulez les distinguer.

`statusCode` et `body` restent la source d'information la plus fiable. Consultez-les plutôt que
`getMessage()` quand l'API renvoie un corps structuré.

### Diagnostiquer un 401

L'API répond souvent à une clé expirée par un corps vide. Le SDK ne se contente alors pas d'un
`HTTP 401` opaque : il nomme la requête, le préfixe de clé utilisé et les causes possibles.

```
Authentication rejected (HTTP 401) — the API returned no error detail.
Request: GET https://api.mitoera.com/api/charts. Key in use: pk_test_… (sandbox).
The API was reached and refused the credentials. Likely causes: the key was revoked
or has expired; keyId and secret come from different key pairs; the key belongs to
another instance than the one baseUrl points at. A wrong baseUrl produces a 404 or a
connection error, never a 401.
```

Seul le préfixe de la clé apparaît : ni le `keyId` complet, ni le `secret` ne sont jamais écrits
dans un message d'erreur.

Un `401` signifie que l'API a bien été jointe et qu'elle a rejeté les identifiants. Une `baseUrl`
erronée produit un `404` ou une erreur de connexion, jamais un `401`.

---

## Flux recommandé (Tapakila / Ticketevent)

```php
// 1. Votre back-end crée une session avant d'afficher la page
$session = $client->sessions->create($eventId);
// → passer $session->sessionToken au front-end (widget JS)

// 2. L'utilisateur sélectionne des sièges dans le widget
// → le widget gère le hold automatiquement via la session

// 3. L'utilisateur valide → votre back-end confirme la réservation
$book = $client->holds->book(
    $session->eventId,
    $selectedSeatKeys,   // transmis par le front via votre API
    $session->holdToken,
);

// 4. En cas d'abandon ou d'expiration
$client->holds->release($session->eventId, $selectedSeatKeys, $session->holdToken);
```

---

## Tests

```bash
composer install
vendor/bin/phpunit
```

30 tests · 119 assertions.
