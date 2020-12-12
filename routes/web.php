<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\GetDataController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\TrainerPageController;
use App\Http\Controllers\PokemonPageController;
use App\Http\Controllers\RelationshipPageController;
use App\Http\Controllers\BattlePageController;

Route::get('/', [HomePageController::class, 'showPage'])->name('home');

Route::group(['prefix' => 'trainer'], function () {
    Route::get('/', [TrainerPageController::class, 'showTrainerPage'])->name('trainer');                            //訓練家管理頁
    Route::post('/doAddTrainer', [TrainerPageController::class, 'doAddTrainer'])->name('doAddTrainer');             //新增訓練家Controller方法
    Route::post('/doEditTrainer', [TrainerPageController::class, 'doEditTrainer'])->name('doEditTrainer');          //修改訓練家Controller方法
    Route::post('/doRemoveTrainer', [TrainerPageController::class, 'doRemoveTrainer'])->name('doRemoveTrainer');    //刪除訓練家Controller方法
});

Route::group(['prefix' => 'pokemon'], function () {
    Route::get('/', [PokemonPageController::class, 'showPokemonPage'])->name('pokemon');                            //寶可夢管理頁
    Route::post('/doAddPokemon', [PokemonPageController::class, 'doAddPokemon'])->name('doAddPokemon');             //新增寶可夢Controller方法
    Route::post('/doEditPokemon', [PokemonPageController::class, 'doEditPokemon'])->name('doEditPokemon');          //修改寶可夢Controller方法
    Route::post('/doRemovePokemon', [PokemonPageController::class, 'doRemovePokemon'])->name('doRemovePokemon');    //刪除寶可夢Controller方法
});

Route::group(['prefix' => 'relationship'], function () {
    Route::get('/', [RelationshipPageController::class, 'showRelationshipPage'])->name('relationship');             //持有寶可夢之訓練家選擇頁
    Route::group(['prefix' => '{trainer_id}'], function () {
        Route::get('/', [RelationshipPageController::class, 'showRelationshipMgrPage'])->name('relationship_mgr');                          //持有寶可夢之訓練家管理頁
        Route::post('/doAddRelationship', [RelationshipPageController::class, 'doAddRelationship'])->name('doAddRelationship');             //新增持有寶可夢Controller方法
        Route::post('/doEditRelationship', [RelationshipPageController::class, 'doEditRelationship'])->name('doEditRelationship');          //修改持有寶可夢之寶可夢內容Controller方法
        Route::post('/doRemoveRelationship', [RelationshipPageController::class, 'doRemoveRelationship'])->name('doRemoveRelationship');    //刪除持有寶可夢Controller方法
    });
});

Route::group(['prefix' => 'battle'], function () {
    Route::group(['prefix' => 'report'], function () {
        Route::get('/', [BattlePageController::class, 'showBattleReportPage'])->name('battleReport');             //對戰紀錄檢視頁
    });
    Route::get('/', [BattlePageController::class, 'showBattlePage'])->name('battle');                                   //對戰紀錄檢視頁
    Route::post('/doAddBattle', [BattlePageController::class, 'doAddBattle'])->name('doAddBattle');                     //修改對戰紀錄Controller方法
    Route::post('/doEditBattle', [BattlePageController::class, 'doEditBattle'])->name('doEditBattle');                  //修改對戰紀錄Controller方法
    Route::post('/doRemoveBattle', [BattlePageController::class, 'doRemoveBattle'])->name('doRemoveBattle');            //修改對戰紀錄Controller方法
});

Route::group(['prefix' => 'api/datas/'], function () {
    Route::get('trainers', [GetDataController::class, 'getTrainers'])->name('api.datas.trainers');                //取得所有訓練家資料 [JSON 格式]
    Route::get('pokemons', [GetDataController::class, 'getPokemons'])->name('api.datas.pokemons');                //取得所有寶可夢資料 [JSON 格式]
    Route::get('relationships', [GetDataController::class, 'getRelationships'])->name('api.datas.relationships'); //取得所有持有寶可夢資料 [JSON 格式]
    Route::get('battle_report', [GetDataController::class, 'getBattleReport'])->name('api.datas.battleReport');   //取得所有對戰統計資料 [JSON 格式]
    Route::get('battles', [GetDataController::class, 'getBattles'])->name('api.datas.battles');                   //取得所有對戰資料 [JSON 格式]
});