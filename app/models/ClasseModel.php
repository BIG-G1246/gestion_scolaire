<?php
class ClasseModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function tous(): array
    {
        return $this->pdo->query("SELECT id, nom FROM classes ORDER BY nom")->fetchAll();
    }

    public function parId(int $id): ?array
    {
        $req = $this->pdo->prepare("SELECT id, nom FROM classes WHERE id = :id");
        $req->execute(["id" => $id]);
        return $req->fetch() ?: null;
    }
}