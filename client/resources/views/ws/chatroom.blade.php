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
                                    <li style="border-bottom: #ced4da 1px solid;" class="col-md-12 row active" style="height: 40px" v-for="(room, key, index) in roomList" v-on:click="set(key)" >
                                        <span class="col-md-4" style="margin-bottom: 0"><h1 style="line-height: 60px;overflow: hidden;margin-bottom: 0">唐</h1></span>
                                        <span class="col-md-8">
                                        @{{ index }}-@{{key}}-@{{room.name}}<br>ddd
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
                                                        @{{record.msg}}
                                                        <span class="arrow"></span>
                                                    </div>
                                                </div>
                                                <div v-if="record.from_id != 'me'">
                                                    <div class="receive">
                                                        @{{record.msg}}
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
                                        <textarea class="form-control" rows="4" style="resize: none"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" style="text-align: right">
                                        <div class="col-md-11"></div>
                                        <button class="btn btn-sm btn-warning" style="margin-top:10px">发送</button>
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
        var room = new Vue({
            el: '#room-list',
            data: {
                roomList: [
                    {name: 'zhu'},
                    {name: 'zhu1'},
                    {name: 'zhu2'}
                ],
                test: 'ddd',
                currentKey: null
            },
            methods: {
                set: function (key) {
                    if (key==0)return ;
                    roomList = this.roomList.concat();
                    room = this.roomList[key];
                    roomList.splice(key,1);
                    roomList.unshift(room);
                    this.roomList = roomList;
                    console.log(roomList);
                    this.currentKey = key;
                }
            }
        });
        var record = new Vue({
            el: "#record",
            data: {
                recordArr: [
                    {msg: "msgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsg",from_id:'me'},
                    {msg: 'msgmsg',from_id:'else'},
                    {msg: "msgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsg",from_id:'me'},
                    {msg: 'msgmsg',from_id:'else'},
                    {msg: "msgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsg",from_id:'me'},
                    {msg: 'msgmsg',from_id:'else'},
                    {msg: "msgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsgmsg",from_id:'me'},
                    {msg: 'msgmsg',from_id:'else'}
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
        }
    </script>
@endsection