<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .logo {
            position: absolute;
            top: 0;
            left: 0;
            padding: 15px;
            height: 55px;
            width: 55px;
        }

        .logo img {
            height: 100%;
            width: 100%;
            object-fit: contain;
        }

        h1, h2, h3 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            text-align: left;
        }
        tfoot th {
            text-align: right;
        }
        p {
            margin-bottom: 10px;
        }


        .bottom-section {
            background-color: #f5f5f5;
            padding: 5px 15px; 
            border: 1px solid #779bfdbe;
            border-radius: 5px;
            box-shadow: 0 0 5px #f6f6f6;
            font-size: small;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://rapidcrewtech.com/images/logo.png" alt="T.R.C">
        </div>
        @if(isset($admin) and $admin)
            <h1 style="text-align: center; color: #007bff;">New Lap-Exchange Order</h1>
        @else
            <h1 style="text-align: center; color: #007bff;">Order Confirmation Alert</h1>
        @endif

        <p>
            @if(isset($admin) and $admin)
                <b style="text-align: center; color: #007bff;">From: </b>
            @else
                <b style="text-align: center; color: #007bff;">Hello</b>
            @endif 
            <strong>
                @if(isset($admin) and $admin)    
                    <span>{{ $name }}</span>
                    <span>{{ $phone_number }}</span>
                    <span>{{ $email }}</span>
                @endif
            </strong>
        </p>

        @if(isset($admin) and $admin)
        <p>
            <b style="text-align: center; color: #007bff;">Merchant: </b>
            
            <strong>
                    <span>{{ $product->user->name }}</span>
                    <span>{{ $product->user->phone_number }}</span>
                    <span>{{ $product->user->email }}</span>
            </strong>
        </p>
        <p>Hello Admin, our system has detected a new order on the Rapid Crew Tech Lap-Exchange platform. Below are the details of the order:</p>
        @else
        <p>Thank you for using Lap-Exchange. Feel free to contact the merchant bellow for further negotiation:</p>
        <p>
            <b style="text-align: center; color: #007bff;">Merchant: </b>
            
            <strong>
                    <span>{{ $product->user->name }}, </span>
                    <span>{{ $product->user->phone_number }}, </span>
                    <span>{{ $product->user->email }}</span>
            </strong>
        </p>
        <p style="color: red">Proceed with caution, meet up with merchants only in crowded or public places, please feel free to contact The Rapid Crew on this number (0501107181) for further assistance </p>
        <p>Below are the details of your order:</p>
        @endif 


        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>${{ $product->price }}</td>
                </tr>

                
            </tbody>
        </table>
        @if(isset($admin) and $admin)
            
        @else
            <p>Thank you for shopping with us.</p>
        @endif
        

        <p>Regards,<br>
        Rapid Crew Tech</p>
    </div>
</body>
</html>
