<?php

namespace App\Http\Controllers;

use mysqli;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GetDataController extends Controller {

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

    /* ========== 取得所有訓練家資料 ========== */
    public function getTrainers(Request $request) {
        $trainers = array();

        // 查詢所有訓練家的資料
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT * FROM trainer
        SQL;
        $result = $mysqli->query($sql);
        $this->closeDBConnection($mysqli);

        // 處理查詢結果格式
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($trainers, [
                    "trainer_id" => $row["trainerID"],
                    "trainer_name" => $row["trainerName"],
                    "mail" => $row["mail"],
                    "tel" => $row["tel"],
                ]);
            }
        }

        return json_encode($trainers, true);
    }

    /* ========== 取得所有寶可夢資料 ========== */
    public function getPokemons(Request $request) {
        $pokemons = array();

        // 查詢所有寶可夢的資料，並將多屬性的寶可夢屬性合併成一欄，依寶可夢編號進行分組
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT p.pokemonID,p.pokemonName, p.description,
                   GROUP_CONCAT(t.typeID) AS typeID, 
                   GROUP_CONCAT(t.typeName) AS typeName
            FROM pokemon p, pokemontype pt, type t
            WHERE p.pokemonID = pt.pokemonID AND pt.typeID = t.typeID
            GROUP BY p.pokemonID
        SQL;
        $result = $mysqli->query($sql);
        $this->closeDBConnection($mysqli);

        // 處理查詢結果格式
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

        return json_encode($pokemons, true);
    }

    /* ========== 取得某訓練家所有寶可夢資料 ========== */
    public function getRelationships(Request $request) {
        // -------------------- 從GET取得WHERE語句參數 --------------------
        $trainer_id = $request->trainer_id;

        // -------------------- 查詢資料 --------------------
        $pokemons = array();

        // 查詢某訓練家持有寶可夢的資料，並將多屬性的寶可夢屬性合併成一欄，依寶可夢編號進行分組
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT p.pokemonID, p.pokemonName, p.description, 
                   GROUP_CONCAT(t.typeID) AS typeID, 
                   GROUP_CONCAT(t.typeName) AS typeName, 
                   tr.trainerName
            FROM pokemon p, pokemontype pt, type t, trainer tr, relationship r
            WHERE p.pokemonID = pt.pokemonID AND pt.typeID = t.typeID AND
                  r.pokemonID = p.pokemonID AND r.trainerID = tr.trainerID AND tr.trainerID = ?
            GROUP BY p.pokemonID
        SQL;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("s", $trainer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->closeDBConnection($mysqli);

        // 處理查詢結果格式
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($pokemons, [
                    "pokemon_id" => $row["pokemonID"],
                    "pokemon_name" => $row["pokemonName"],
                    "description" => $row["description"],
                    "pokemon_type_id" => explode(",", $row["typeID"]),
                    "pokemon_type_name" => explode(",", $row["typeName"]),
                    "trainer_name" => $row["trainerName"]
                ]);
            }
        }

        return json_encode($pokemons, true);
    }

    /* ========== 取得對戰統計資料 ========== */
    public function getBattleReport(Request $request) {
        // -------------------- 從GET取得WHERE語句參數並進行格式處理 --------------------
        $search = [
            'trainerID'   => $request->trainer_id?? "",
            'trainerName' => $request->trainer_name?? "",
            'orderBy' => $request->order_by?? "idasc"
        ];
        $orderByMap = [
            'idasc' => "t.trainerID ASC",
            'iddesc' => "t.trainerID DESC",
            'winasc' => "wins ASC",
            'windesc' => "wins DESC",
            'loseasc' => "loses ASC",
            'losedesc' => "loses DESC",
        ];
        $search['orderBy'] = $orderByMap[$search['orderBy']];

        // -------------------- 查詢資料 --------------------
        $battles = array();

        // 查詢對戰統計的資料，統計訓練家的總勝敗數，且若有收到$_GET資料，依其資料進行資料篩選或排序
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT t.trainerID, t.trainerName, 
                   COUNT(CASE t.trainerID WHEN b.winnerID THEN 1 END) AS wins,
                   COUNT(CASE t.trainerID WHEN b.loserID THEN 1 END) AS loses
            FROM trainer t
            LEFT JOIN battle b ON (t.trainerID = b.winnerID OR t.trainerID = b.loserID)
            WHERE t.trainerID LIKE concat('%', ? ,'%') AND t.trainerName LIKE concat('%', ? ,'%')
            GROUP BY t.trainerID
        SQL;
        $sql .= " ORDER BY ".$search['orderBy'];
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ss", $search['trainerID'], $search['trainerName']);
        $stmt->execute();
        $result = $stmt->get_result();
        $this->closeDBConnection($mysqli);

        // 處理查詢結果格式
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($battles, [
                    "trainer_id" => $row["trainerID"],
                    "trainer_name" => $row["trainerName"],
                    "trainer_wins" => $row["wins"],
                    "trainer_loses" => $row["loses"]
                ]);
            }
        }

        return json_encode($battles, true);
    }

    /* ========== 取得所有對戰資料 ========== */
    public function getBattles() {
        $battles = array();

        // 查詢所有對戰的資料
        $mysqli = $this->getDBConnection();
        $sql = <<<SQL
            SELECT b.battleID, b.battleDatetime, 
                   tW.trainerID AS winnerID,  W.trainerName AS winnerName, 
                   tL.trainerID AS loserID,  tL.trainerName AS loserName, 
                   b.description
            FROM battle b, trainer tW, trainer tL
            WHERE b.winnerID=tW.trainerID AND b.loserID=tL.trainerID
            ORDER BY b.battleID DESC
        SQL;
        $result = $mysqli->query($sql);
        $this->closeDBConnection($mysqli);

        // 處理查詢結果格式
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                array_push($battles, [
                    "battle_id" => $row["battleID"],
                    "battle_datetime" => $row["battleDatetime"],
                    "winner_id" => $row["winnerID"],
                    "winner_name" => $row["winnerName"],
                    "loser_id" => $row["loserID"],
                    "loser_name" => $row["loserName"],
                    "battle_description" => $row["description"]
                ]);
            }
        }

        return json_encode($battles, true);
    }
}
