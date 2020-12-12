@extends('layouts.base')
@section('nav_battle', 'active')
@section('content')
    
    <div id="toolbar">
        <button id="btn_add" type="button" class="btn bg-blue-600 text-white" onclick="add();">
            <i class="fas fa-plus"></i>&nbsp;新增
        </button>
        <button id="btn_edit" type="button" class="btn bg-teal-600 text-white" onclick="edit();">
            <i class="fas fa-edit"></i>&nbsp;修改
        </button>
        <button id="btn_remove" type="button" class="btn bg-pink-500 text-white" onclick="remove();">
            <i class="fas fa-trash-alt"></i>&nbsp;刪除
        </button>
        <button id="btn_refresh" type="button" class="btn bg-orange-500 text-white" onclick="refresh();">
            <i class="fas fa-sync-alt"></i>&nbsp;刷新
        </button>
    </div>

    <div class="row">
        <h2 class="mb-3">對戰紀錄列表</h2>
        <!-- 對戰紀錄列表 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="battlesTable"></table>
                    </div>
                </div>
            </div>
        </div>
        <!-- #對戰紀錄列表 -->
    </div>

    <!--========== 詳細戰況 ==========-->
    <div id="detailModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-inline">詳細戰況</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="detailPane" class="modal-body text-left">
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
    <!--========== #詳細戰況 ==========-->

    <!--========== 新增資料 ==========-->
    <div id="addModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-inline">新增對戰紀錄</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{route('doAddBattle')}}">
                    @csrf
                    <div class="modal-body text-center">
                        <div class="input-group mb-3">
                            <span class="input-group-text">對戰日期</span>
                            <input type="date" class="form-control" placeholder="對戰日期" aria-label="對戰日期" aria-describedby="battle_datetime" name="battle_datetime">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">勝方訓練家</span>
                            <select class="form-select" aria-label="winner_id" name="winner_id">
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer['trainer_id'] }}">{{ $trainer['trainer_name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">敗方訓練家</span>
                            <select class="form-select" aria-label="loser_id" name="loser_id">
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer['trainer_id'] }}">{{ $trainer['trainer_name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">戰局描述</span>
                            <textarea class="form-control" aria-label="戰局描述" aria-describedby="battle_description" name="battle_description" rows="15"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-blue-500">送出</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--========== #新增資料 ==========-->


    <!--========== 修改資料 ==========-->
    <div id="editModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-inline">編輯對戰紀錄</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{route('doEditBattle')}}">
                    @csrf
                    <div class="modal-body text-center">
                        <div class="input-group mb-3">
                            <span class="input-group-text">對戰編號</span>
                            <input type="text" class="form-control" name="battle_id_displayed" disabled>
                            <input type="text" class="form-control" name="battle_id" hidden>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">對戰日期</span>
                            <input type="date" class="form-control" placeholder="對戰日期" aria-label="對戰日期" aria-describedby="battle_datetime" name="battle_datetime">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">勝方訓練家</span>
                            <select class="form-select" aria-label="winner_id" name="winner_id">
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer['trainer_id'] }}">{{ $trainer['trainer_name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">敗方訓練家</span>
                            <select class="form-select" aria-label="loser_id" name="loser_id">
                                @foreach ($trainers as $trainer)
                                    <option value="{{ $trainer['trainer_id'] }}">{{ $trainer['trainer_name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">戰局描述</span>
                            <textarea class="form-control" aria-label="戰局描述" aria-describedby="battle_description" name="battle_description" rows="15"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-blue-500">送出</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--========== #修改資料 ==========-->
@stop

@section('custom_script')
    @include('layouts.sweetAlert')
    
    <script>
        /*========== 將從controller得到的資料放到bootstrap-table上 ========== */
        $('#battlesTable').bootstrapTable({
            url: "{{route('api.datas.battles')}}",
            method: "get",
            dataType: "json",
            uniqueId: "id",
            toolbar : "#toolbar",
            cache: false,
            pagination: true,
            singleSelect: true,
            clickToSelect: true,
            pageSize: 10,
            pageList: [5, 10, 15, 20],
            columns: [{
                width: '5',
                checkbox: true
            },{
                sortable: true,
                field: 'battle_id',
                title: '對戰編號'
            },{
                sortable: true,
                field: 'battle_datetime',
                title: '對戰時間'
            },{
                sortable: true,
                field: 'winner_name',
                title: '勝方訓練家'
            },{
                sortable: true,
                field: 'loser_name',
                title: '敗方訓練家'
            },{
                field: 'battle_description',
                title: '戰局描述',
                formatter: function(value, row, index) {
                    var len = 50; // 超過50個字以"..."取代
                    var text = value;
                    if(value.length>len)
                        text = value.substring(0,len-1) + "...";
                    return text;
                }
            },{
                formatter: function(value, row, index) {
                    var html = '<button type="button" class="btn btn-outline-blue-600" onclick="detail('+index+');">詳細戰況</button>';
                    return html;
                }
            }]
        })

        /*========== 新增 ========== */
        function add() {
            $("#addModal").modal('show');
        }

        /*========== 修改 ========== */
        function edit() {
            var row = $("#battlesTable").bootstrapTable('getSelections')[0];
            if(row != null){
                // 將選定的寶可夢的目前的id、name、description、type設定到modal元件上
                $('#editModal input[name^="battle_id"]').val(row.battle_id);
                $('#editModal input[name="battle_datetime"]').val(row.battle_datetime);
                $('#editModal select[name="winner_id"]').val(row.winner_id);
                $('#editModal select[name="loser_id"]').val(row.loser_id);
                $('#editModal textarea[name="battle_description"]').val(row.battle_description);
                $("#editModal").modal('show');
            } else {
                Swal.fire('沒有選取項目','','warning');
            }
        }

        /*========== 新增 ========== */
        function remove() {
            var row = $("#battlesTable").bootstrapTable('getSelections')[0];
            if(row != null){
                Swal.fire({
                    title: "警告！",
                    html: "確定要刪除？",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "刪除", 
                    confirmButtonColor: "#dc3545",
                    cancelButtonText: "返回",
                }).then(function(result){
                    if(result.value) {
                        // 執行刪除
                        $.ajax({
                            type: "POST",
                            url: "{{route('doRemoveBattle')}}",
                            data: {
                                'battle_id': row.battle_id,
                                '_token': '{{csrf_token()}}'
                            }
                        }).done(function(response) {
                            Swal.fire(response['title'], response['message'], response['type']);
                            $('#battlesTable').bootstrapTable('refresh');
                        })
                    };
                })
            } else {
                Swal.fire('沒有選取項目','','warning');
            }
        }

        /*========== 重新整理 ========== */
        function refresh(){
            $('#battlesTable').bootstrapTable('refresh');
        };

        /*========== 重新整理 ========== */
        function detail(index){
            $("#battlesTable").bootstrapTable('check', index);
            var row = $("#battlesTable").bootstrapTable('getSelections')[0];
            $("#detailPane").html(row.battle_description.replace(/(?:\r\n|\r|\n)/g, '<br />'));
            $("#detailModal").modal('show');
        };
    </script>
@stop