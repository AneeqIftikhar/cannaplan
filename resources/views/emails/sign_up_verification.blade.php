<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Open Canna Plans</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <!-- Styles -->

</head>
<body style="font-family: Arial; font-size: 12px;">
    <div>
        <p>
            Hello {{$data["name"]}},
        </p>
        <p>
            You have signed up for Open Canna Plans, please follow the link below to activate your account.
        </p>

        <p>
            <table>
                <tr>
                    <td style="background-color: #4ecdc4;border-color: #4c5764;border: 2px solid #45b7af;padding: 10px;text-align: center;">
                        <a style="display: block;color: #ffffff;font-size: 12px;text-decoration: none;text-transform: uppercase;" href='http://localhost/home'>
                            Click here to activate your account
                        </a>
                    </td>
                </tr>
            </table>
        </p>
    </div>


</body>
</html>