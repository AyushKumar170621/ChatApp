<?php
session_start();
if (!isset($_SESSION['user_data'])) {
    header('Location: index.php');
}

$user_data = $_SESSION['user_data'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <style>
        .user-list {
            height: 100vh;
            overflow-y: auto;
        }

        .chat-space {
            height: 80vh;
            overflow-y: auto;
        }

        .chat-input {
            position: absolute;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ChatApp</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <?php
            $login_user_id = '';
            $token = '';
            foreach ($user_data as $key => $value) {
                $login_user_id = $value['id'];
                $token = $value['token'];
                ?>
                <input type="hidden" name="login_user_id" id="login_user_id" value="<?php echo $login_user_id; ?>">
                <input type="hidden" name="is_active_chat" id="is_active_chat" value="No">
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <img src="<?php echo $value['profile']; ?>" alt="Profile Photo" class="rounded-circle"
                                    width="30" height="30">
                                <?php echo $value['name']; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <input type="button" class="btn btn-primary mt-2 mb-2" value="Logout" name="logout"
                                id="logout" />
                        </li>
                    </ul>
                </div>
                <?php
            }
            require_once '../database/ChatUser.php';
            $chatuser = new ChatUser();
            $chatuser->setUserId($login_user_id);
            $user_data = $chatuser->get_all_users_data_with_status();
            ?>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- User List -->
            <div class="col-3 user-list border-end">
                <h4 class="mt-4">User List</h4>
                <div class="list-group">
                    <?php
                    foreach ($user_data as $key => $user) {
                        $icon = '<i class="fa fa-circle text-danger"></i>';
                        if ($user['status'] == 'Active') {
                            $icon = '<i class="fa fa-circle text-success"></i>';
                        }
                        if ($user['user_id'] != $login_user_id) {
                            if ($user['count_status'] > 0) {
                                $total_unread_message = '<span class="badge badge-danger badge-pill">' . $user['count_status'] . '</span>';
                            } else {
                                $total_unread_message = '';
                            }

                            echo "<a class='list-group-item list-group-action select_user'
                            style='cursor:pointer' data-userid = '" . $user['user_id'] . "'>
                            <img src='" . $user['user_photo'] . "' class='img-fluid rounded-circle
                            img-thumbnai' width='50' />
                            <span class='ml-1'>
                                <strong>
                                    <span id='list_user_name_" . $user["user_id"] . "'>" . $user["first_name"] . ' ' . $user["last_name"] . "</span>
                                    <span id='userid_" . $user["user_id"] . "'>" . $total_unread_message . "</span>
                                    
                                </strong>
                                </span>
                                <span class= 'mt-2 float-right' id='userstatus_" . $user["user_id"] . "'>" . $icon . "</span>
                            </a>
                        ";
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Chat Space -->
            <div class="col-9 position-relative" id="chat_area">

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            console.log("Document ready");
            var receiver_userid = '';
            var conn = new WebSocket('ws://localhost:8080?token=<?php echo $token; ?>');
            conn.onopen = function (e) {
                console.log("Connection established!");
            };

            conn.onmessage = function (e) {
                var data = JSON.parse(e.data);
                console.log(data);
                if(data.status == 'Active')
                {
                    $('#userstatus_'+data.user_id_status).html('<i class="fa fa-circle text-success"></i>');
                }
                else if(data.status == 'Inactive')
                {
                    $('#userstatus_'+data.user_id_status).html('<i class="fa fa-circle text-danger"></i>');
                }
                else
                {
                    var row_class= '';
                var background_class = '';
                if(data.from == 'Me')
                {
                    row_class = 'row justify-content-start';
                    background_class = 'alert-primary';
                }
                else
                {
                    row_class = 'row justify-content-end';
                    background_class = 'alert-success';
                }

                if(receiver_userid == data.userId || data.from == 'Me')
                {
                    var html_data='';
                    if($('#is_active_chat').val() == 'Yes')
                    {
                        html_data += `
                            <div class="`+row_class+`">
                                <div class="col-sm-10">
                                    <div class="shadow-sm alert `+background_class+`">
                                        <b>`+data.from+` - <b>`+data.msg+`<br/>
                                        <div class="text-right">
                                            <small><i>`+data.datetime+`</i></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#messages_area').append(html_data);
                        $('#messages_area').scrollTop($('#messages_area')[0].scrollHeight);

                    }
                    else
                    {
                        var count_chat = $('#userid'+data.userId).text();

                        if(count_chat == '')
                        {
                            count_chat=0;
                        }
                        count_chat++;
                        $('#userid'+data.userId).html('<span class="badge badge-danger badge-pill">'+count_chat+'</span>')
                    }
                }
                }
                

            };

            conn.onclose = function (event) {
                console.log('connection close');
            }
            function make_chat_area(user_name) {
                console.log(user_name);
                var html = `
                   <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <b>Chat with <span class="text-danger" id="chat_user_name">`+ user_name + `</span></b>
                        </div>
                        <div>
                            <button type="button" class="close" id="close_chat_area" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="card-body flex-grow-1 overflow-auto" id="messages_area">
                        <!-- Messages will be loaded here -->
                    </div>
                    <div class="card-footer">
                        <form id="chat_form" method="POST">
                            <div class="input-group">
                                <textarea class="form-control" id="chat_message" name="chat_message"
                                          placeholder="Type Message Here" maxlength="255"></textarea>
                                <button type="submit" name="send" id="send" class="btn btn-primary input-group-text">
                                    <i class="fa fa-paper-plane"></i> Send
                                </button>
                            </div>
                            <div id="validation_error" class="text-danger mt-2"></div>
                        </form>
                    </div>
                </div>


                `;
                $('#chat_area').html(html);
                $(document).on('click', '#close_chat_area', function () {
                    $('#chat_area').html('');
                    $('.select_user.active').removeClass('active');
                });


            }
            $(document).on('click', '.select_user', function () {
                receiver_userid = $(this).data('userid');
                var from_user_id = $('#login_user_id').val();
                var receiver_user_name = $('#list_user_name_' + receiver_userid).text();
                console.log(receiver_user_name);
                $('.select_user_active').removeClass('active');
                $(this).addClass('active');
                make_chat_area(receiver_user_name);
                $('#is_active_chat').val('Yes');

                $.ajax({
                    url:"action.php",
                    method:"POST",
                    data:{action:'fetch_chat', to_user_id:receiver_userid,from_user_id:from_user_id},
                    dataType:"JSON",
                    success:function(data)
                    {
                        if(data.length > 0)
                        {
                            var html_data = '';
                            for(var count =0;count < data.length;count++)
                            {
                                var row_class= '';
                                var background_class = '';
                                var user_name = '';
                                if(data[count].sender_id == from_user_id)
                                {
                                    row_class = 'row justify-content-start';
                                    background_class = 'alert-primary';
                                    user_name = 'Me';
                                }
                                else
                                {
                                    row_class = 'row justify-content-end';
                                    background_class = 'alert-success';
                                    user_name = data[count].from_user_name;
                                }

                                html_data +=  `
                                <div class="`+row_class+`">
                                    <div class="col-sm-10">
                                        <div class="shadow alert  `+background_class+`">
                                            <b>`+user_name+` - </b>
                                            `+data[count].message+`<br/>
                                            <div class="text-right">
                                                <small><i>`+data[count].timestamp+`</i></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                `;
                            }
                            $('#userid_'+receiver_userid).html('');
                            $('#messages_area').html(html_data);
                            $('#messages_area').scrollTop($('#messages_area')[0].scrollHeight);

                        }
                    }
                })
            });

            $(document).on('submit', '#chat_form', function (event) {
                event.preventDefault(); // Prevent form submission

                var messageInput = $('#chat_message');
                var messageError = $('#validation_error');
                var messageValue = messageInput.val().trim();
                var user_id = $('#login_user_id').val();

                if (messageValue === '') {
                    messageError.text('Message cannot be empty');
                    messageError.show();
                    messageInput.addClass('is-invalid');
                } else {
                    messageError.text('');
                    messageError.hide();
                    messageInput.removeClass('is-invalid');

                    var data = {
                        userId:user_id,
                        msg:messageValue,
                        receiver_user_id:receiver_userid,
                        command:'private',
                    }
                    
                    conn.send(JSON.stringify(data)); 

                    // Clear the message input field after successful submission
                    messageInput.val('');
                }
            });

            $('#logout').click(function () {
                console.log("Logout button clicked");

                var user_id = $('#login_user_id').val();
                console.log("User ID: " + user_id);

                if (user_id) {
                    $.ajax({
                        url: "action.php",
                        method: "POST",
                        data: { user_id: user_id, action: 'leave' },
                        success: function (data) {
                            console.log("Response received: " + data);
                            var response;
                            try {
                                response = JSON.parse(data);
                            } catch (e) {
                                console.error("Failed to parse JSON response: " + e);
                                return;
                            }

                            if (response.status == 1) {
                                conn.close();
                                console.log("Logout successful, redirecting...");
                                location = "index.php";
                            } else {
                                console.log("Logout failed");
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error: " + status + " - " + error);
                        }
                    });
                } else {
                    console.warn("User ID not found");
                }
            });
        });


    </script>
</body>

</html>