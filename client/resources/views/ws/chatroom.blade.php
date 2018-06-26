@extends("layouts.myapp")

@section('content')
    <style type="text/css">
        body {
            background:#4D4948;
        }

        .send {
            position:relative;
            /*width:150px;*/
            /*height:35px;*/
            background:#F8C301;
            border-radius:5px; /* 圆角 */
            margin:30px auto 0;
            padding-left: 5px;
            padding-right: 5px;
        }
        .receive {
            position:relative;
            /*width:150px;*/
            /*height:35px;*/
            background:#F8C301;
            border-radius:5px; /* 圆角 */
            margin:30px auto 0;
            padding-left: 5px;
            padding-right: 5px;
        }

        .receive-arrow {
            position:absolute;
            top:8px;
            left:-16px; /* 圆角的位置需要细心调试哦 */
            width:0;
            height:0;
            font-size:0;
            border:solid 8px;
            border-color:#FFFFFF #F8C301 #FFFFFF #FFFFFF ;
        }

        .send .arrow {
            position:absolute;
            top:8px;
            right:-16px; /* 圆角的位置需要细心调试哦 */
            width:0;
            height:0;
            font-size:0;
            border:solid 8px;
            border-color:#FFFFFF #FFFFFF #FFFFFF #F8C301;
        }
    </style>


    <div class="container">
        <div class="row justify-content-center ">
            <div class="col-md-10">
                <div class="card" style="width: 100%">
                    <div class="card-header">
                        WEB SOCKET CHAT ROOM
                    </div>
                    <div class="card-body row">
                        <div class="col-md-4" style="border-right: 1px solid #ced4da;overflow-y: scroll">
                            <div id="room-list">
                                <ul class="nav">
                                    <li style="border-bottom: #ced4da 1px solid;" class="col-md-12 row active" style="height: 40px" v-for="(room, key) in roomList" v-on:click="set(room)" >
                                        <span class="col-md-4" style="margin-bottom: 0"><h1 style="line-height: 60px;overflow: hidden;margin-bottom: 0">兰</h1></span>
                                        <span class="col-md-8">
                                        @{{key}}-@{{room.name}}<br>@{{ room.msg_num }}
                                    </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12" id="record" style="overflow-y: scroll">
                                    <ul>
                                        <li class="col-md-12" v-for="record in recordArr" style="list-style: none;line-height: 30px;" v-bind:style="{float:msgFloat(record)}">
                                            <div v-bind:style="{float:msgFloat(record)}">
                                                <div v-if="record.from_id == 'me'">
                                                    <div class="send">
                                                        @{{record.content}}
                                                        <span class="arrow"></span>
                                                    </div>
                                                </div>
                                                <div v-if="record.from_id != 'me'">
                                                    <div class="receive">
                                                        @{{record.content}}
                                                        <div class="receive-arrow"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div id="chatInput">
                                <br>
                                <div class="row">
                                    <div class="col-md-12 pull-right ">
                                        <textarea id="msg" class="form-control" rows="4" style="resize: none"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" style="text-align: right">
                                        <div class="col-md-11"></div>
                                        <button onclick="sendMsg()" class="btn btn-sm btn-warning" style="margin-top:10px">发送</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script
            src="https://code.jquery.com/jquery-2.2.4.min.js"
            integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
            crossorigin="anonymous"></script>
    <script>
        var ws;
        var wsPing;
        var wsToken;
        var wsHost;
        var wsPort;
        var url = 'http://127.0.0.1/socket/client/public'

        function ajaxToken() {
            console.log(url);
            $.get(url+"/ws/token",function (data) {
                wsToken = data.token;
                wsHost = data.host;
                wsPort = data.port;
                console.log(wsToken);
                wsConnect();
            },'json');
        }
        function wsConnect() {
            try {
                ws = new WebSocket("ws://"+wsHost+":"+wsPort);
                ws.onopen = function (event) {
                    console.log('Socket Connect');
                    sendLogin();
                    wsPing = setInterval('pingWs()',3000);
                }
            }catch (e){}
            ws.onmessage = function (evt) {
                var msgJson = JSON.parse(evt.data);
                switch (msgJson.type){
                    case 'pong':
                        console.log('pong');
                        break;
                    case 'msg':
                        console.log('發送者ID：'+msgJson.from_id+msgJson.content);
                        record.recordArr.push(msgJson);
                        break;
                    case 'group':
                        console.log('GROUPID:'+msgJson.to_id)
                    default:
                        console.log(msgJson.content)
                }
                scrollRecord()
            }
            ws.onclose = function (p1) {
                clearInterval(wsPing);
                console.log("Connection closed!");
            }
            ws.onerror = function (event) {
                clearInterval(wsPing);
                console.log("Connect error!");
            }
        }
        function sendMsg() {
            var msg = $("#msg").val();
//            console.log(room.currentUser.id );return
            var msg = {'type':'msg','content':msg,'to_id':room.currentUser.id,'from_id':'me'}
//            console.log(msg);return;

            msg1 = JSON.stringify(msg);
            ws.send(msg1);
            record.recordArr.push(msg)
            scrollRecord()

        }
        function sendImg() {
            var file = document.querySelector("input[type='file']").files[0];
            UpladFile(file);
        }
        function pingWs() {
            var msg = {'type':'ping','msg':'ping'};
            ws.send(JSON.stringify(msg));
        }
        function sendLogin() {
            var msg = {'type':'login','msg':'','token':wsToken};
            ws.send(JSON.stringify(msg));
        }
    {{--</script>--}}
    {{--<script>--}}
        var room = new Vue({
            el: '#room-list',
            data: {
                roomList: [

                ],
                test: 'ddd',
                currentUser: null
            },
            methods: {
                set: function (room) {
                    this.currentUser = room;
//                    roomList = this.roomList.concat();
//                    room = this.roomList[key];
//                    roomList.splice(key,1);
//                    roomList.unshift(room);
//                    this.roomList = roomList;
//                    console.log(roomList);
//                    this.currentKey = key;
                }
            }
        });
        var record = new Vue({
            el: "#record",
            data: {
                recordArr: [
                ]
            },
            methods: {
                msgFloat: function (record) {
                    if (record.from_id == 'me')
                        return 'right';
                    return 'left';
                }
            }
        })
        function selectRoom(key) {
            console.log(key);
        }
        $(window).ready(function () {
            initSize();
            ajaxToken();
            initChatRoom();
        });
        $(window).resize(function () {
           initSize();
        });
        function initSize()
        {
            var width = $('.card-body').width()
            var height = width/16*9;
            $('.card-body').height(height);
            var inputHeight = $('#chatInput').height();
            $('#record').height(height-inputHeight)
            msgWidth = $('#record').width()/2
            $('.send').css({'max-width':msgWidth})
            $('.receive').css({'max-width':msgWidth})
            scrollRecord();
        }
        function scrollRecord() {
            var recordDiv = document.getElementById('record');
            recordDiv.scrollTop = recordDiv.scrollHeight;
        }
        function initChatRoom() {
            getUserList();
        }
        function getUserList() {
            $.get(url+'/ws/user/list',function (list) {
                room.roomList = list;
                console.log(list)
            },'json');
        }
    </script>


@endsection