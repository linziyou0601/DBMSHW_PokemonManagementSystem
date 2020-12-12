@extends('layouts.base')
@section('nav_trainer', 'active')
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
        <h2 class="mb-3">訓練家列表</h2>
        <!-- 訓練家列表 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="trainersTable"></table>
                    </div>
                </div>
            </div>
        </div>
        <!-- #訓練家列表 -->
    </div>

    <!--========== 新增資料 ==========-->
    <div id="addModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-inline">新增訓練家</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{route('doAddTrainer')}}">
                    @csrf
                    <div class="modal-body text-center">
                        <div class="input-group mb-3">
                            <span class="input-group-text">編號</span>
                            <input type="text" class="form-control" placeholder="由8~20字元的英數字組成" aria-label="訓練家編號" aria-describedby="trainer_id" name="trainer_id">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">名稱</span>
                            <input type="text" class="form-control" placeholder="訓練家名稱" aria-label="訓練家名稱" aria-describedby="trainer_name" name="trainer_name">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">電子信箱</span>
                            <input type="text" class="form-control" placeholder="pokemon@gmail.com" aria-label="訓練家電子信箱" aria-describedby="trainer_mail" name="trainer_mail">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">電話</span>
                            <input type="text" class="form-control" placeholder="訓練家電話" aria-label="訓練家電話" aria-describedby="trainer_tel" name="trainer_tel">
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-inline">編輯訓練家</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{route('doEditTrainer')}}">
                    @csrf
                    <div class="modal-body text-center">
                        <div class="input-group mb-3">
                            <span class="input-group-text">編號</span>
                            <input type="text" class="form-control" name="trainer_id_displayed" disabled>
                            <input type="text" class="form-control" name="trainer_id" hidden>
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">名稱</span>
                            <input type="text" class="form-control" placeholder="訓練家名稱" aria-label="訓練家名稱" aria-describedby="trainer_name" name="trainer_name">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">電子信箱</span>
                            <input type="text" class="form-control" placeholder="pokemon@gmail.com" aria-label="訓練家電子信箱" aria-describedby="trainer_mail" name="trainer_mail">
                        </div>

                        <div class="input-group mb-3">
                            <span class="input-group-text">電話</span>
                            <input type="text" class="form-control" placeholder="訓練家電話" aria-label="訓練家電話" aria-describedby="trainer_tel" name="trainer_tel">
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
        $('#trainersTable').bootstrapTable({
            url: "{{route('api.datas.trainers')}}",
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
                field: 'trainer_id',
                title: '訓練家編號'
            },{
                sortable: true,
                field: 'trainer_name',
                title: '訓練家名稱'
            },{
                sortable: true,
                field: 'mail',
                title: '電子信箱'
            },{
                sortable: true,
                field: 'tel',
                title: '電話'
            },{
                formatter: function(value, row, index) {
                    var html = '<a href="{{ route('relationship_mgr', '') }}/'+row.trainer_id+'" type="button" class="btn btn-outline-blue-600">持有寶可夢</button>';
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
            var row = $("#trainersTable").bootstrapTable('getSelections')[0];
            if(row != null){
                // 將選定的寶可夢的目前的id、name、description、type設定到modal元件上
                $('#editModal input[name^="trainer_id"]').val(row.trainer_id);
                $('#editModal input[name="trainer_name"]').val(row.trainer_name);
                $('#editModal input[name="trainer_mail"]').val(row.mail);
                $('#editModal input[name="trainer_tel"]').val(row.tel);
                $("#editModal").modal('show');
            } else {
                Swal.fire('沒有選取項目','','warning');
            }
        }

        /*========== 新增 ========== */
        function remove() {
            var row = $("#trainersTable").bootstrapTable('getSelections')[0];
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
                            url: "{{route('doRemoveTrainer')}}",
                            data: {
                                'trainer_id': row.trainer_id,
                                '_token': '{{csrf_token()}}'
                            }
                        }).done(function(response) {
                            Swal.fire(response['title'], response['message'], response['type']);
                            $('#trainersTable').bootstrapTable('refresh');
                        })
                    };
                })
            } else {
                Swal.fire('沒有選取項目','','warning');
            }
        }

        /*========== 重新整理 ========== */
        function refresh(){
            $('#trainersTable').bootstrapTable('refresh');
        };
    </script>
@stop