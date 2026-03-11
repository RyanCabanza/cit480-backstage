<?php
declare(strict_types=1);

final class DbSessionHandler implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo) {}
    public function open(string $path, string $name): bool { return true; }
    public function close(): bool {return true;}
    public function read(string $id): string
    {
        $stmt = $this->pdo->prepare(
            "SELECT data FROM sessions
             WHERE id = :id AND timestamp >= :min_ts
             LIMIT 1"
        );

        $stmt->execute([
            ':id' => $id,
            ':min_ts' => time() - (int)ini_get('session.gc_maxlifetime'),

        ]);
         $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (string)$row['data'] : '';
    }

    public function write(string $id, string $data): bool
    {
        $userId = $_SESSION['user_id'] ?? null;

        $stmt = $this->pdo->prepare(
            "INSERT INTO sessions (id, user_id, data, timestamp)
             VALUES (:id, :user_id, :data, :ts)
             ON DUPLICATE KEY UPDATE
               user_id = VALUES(user_id),
               data = VALUES(data),
               timestamp = VALUES(timestamp)"
        );

        return $stmt->execute([
            ':id' => $id,
            ':user_id' => $userId,
            ':data' => $data,
            ':ts' => time(),
        ]);
    }

    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE timestamp < :min_ts");
        $stmt->execute([':min_ts' => time() - $max_lifetime]);
        return $stmt->rowCount();
    }
}
    
