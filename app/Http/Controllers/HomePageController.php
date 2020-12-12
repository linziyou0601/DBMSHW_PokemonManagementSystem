<?php

namespace App\Http\Controllers;

use mysqli;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomePageController extends Controller {

    /* ========== 建立DB連線 ========== */
    private function getDBConnection() {
        $mysqli = new mysqli(env("DB_HOST"), env("DB_USERNAME"), env("DB_PASSWORD"), env("DB_DATABASE"));
        $mysqli->query('SET NAMES utf8');
        return $mysqli;
    }

    /* ========== 關閉DB連線 ========== */
    private function closeDBConnection(mysqli $mysqli) {
        $mysqli->close();
    }

    /* ========== 首頁 ========== */
    public function showPage() {
        $pkm_count = 0;
        $trainer_count = 0;
        $battle_count = 0;
        $mvp_wins = 0;
        $mvp_name = "";
        $pokemons = array();

        // 查詢各式數量
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT *
            FROM (SELECT COUNT(*) as pkm_count FROM pokemon) AS pkm_count
            CROSS JOIN (SELECT COUNT(*) as trainer_count FROM trainer) AS trainer_count
            CROSS JOIN (SELECT COUNT(*) as battle_count FROM battle) AS battle_count 
            CROSS JOIN (SELECT t.trainerName, COUNT(b.winnerID) AS wins
                        FROM trainer t LEFT JOIN battle b ON t.trainerID = b.winnerID
                        GROUP BY t.trainerID ORDER BY wins DESC LIMIT 1) AS most_win
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            $pkm_count = $row["pkm_count"];
            $trainer_count = $row["trainer_count"];
            $battle_count = $row["battle_count"];
            $mvp_wins = $row["wins"];
            $mvp_name = $row["trainerName"];
        }

        // 查詢前十筆寶可夢
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT p.pokemonID,p.pokemonName, p.description,
                   GROUP_CONCAT(t.typeID) AS typeID, 
                   GROUP_CONCAT(t.typeName) AS typeName
            FROM pokemon p, pokemonType pt, type t
            WHERE p.pokemonID = pt.pokemonID AND pt.typeID = t.typeID
            GROUP BY p.pokemonID LIMIT 10
        SQL;
        $result = $mysqli->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($pokemons, [
                    "pokemon_id" => $row["pokemonID"],
                    "pokemon_name" => $row["pokemonName"],
                    "description" => $row["description"],
                    "pokemon_type_id" => explode(",", $row["typeID"]),
                    "pokemon_type_name" => explode(",", $row["typeName"])
                ]);
            }
        }
        $this->closeDBConnection($mysqli);

        // View的資料
        $data = [
            'pkm_count' => $pkm_count,
            'trainer_count' => $trainer_count,
            'battle_count' => $battle_count,
            'mvp_wins' => $mvp_wins,
            'mvp_name' => $mvp_name,
            'pokemons' => $pokemons
        ];

        return view('index', $data);
    }
}
