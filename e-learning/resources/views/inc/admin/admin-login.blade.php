<style>

    input[type=text], input[type=password] {

        width: 200px;
        padding: 1px 10px;
        margin: 8px 0;
        font: normal 80%/100% 'Verdana';
    }

    button {
        background-color: #6b9dbb;
        color: white;
        padding: 5px;
        margin: 8px 0;
        border: none;
        cursor: pointer;
        width: 30%;
    }
    button:hover {
        background: #2b2b2b;
    }
    .container {
        padding: 5px;
    }
   



</style>


<div class="well" id="well">
    <div class="row">



        <div class="col-lg-10">
            <form action="{{route('managerLogin')}}" method="post">
                <label><b>Username</b></label>
                <input type="text" placeholder="Enter Username" name="account" required>

                <label><b>Password</b></label>
                <input type="password" placeholder="Enter Password" name="pw" required>


                <div>

                    <button type="submit">Login</button>
                </div>


                <div>
                    <span class="psw"><a href="#">Reset password</a></span>
                </div>
            </form>
        </div>
    </div>
</div>