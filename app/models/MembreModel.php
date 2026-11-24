<?php
class MembreModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function parLogin(string $login): ?array
    {
        $req = $this->pdo->prepare(
            "SELECT id, login, mdp_hash, role FROM membres WHERE login = :login"
        );
        $req->execute(["login" => $login]);
        return $req->fetch() ?: null;
    }
}