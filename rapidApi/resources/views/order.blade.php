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
            <h1 style="text-align: center; color: #007bff;">New Order</h1>
        @else
            <h1 style="text-align: center; color: #007bff;">New Order Alert</h1>
        @endif

        <p>
            @if(isset($admin) and $admin)
                <h1 style="text-align: center; color: #007bff;">From: </h1>
            @else
                <h1 style="text-align: center; color: #007bff;">Hello</h1>
            @endif 
            <strong>
                {{ $order->user->name }}, 
                @if(isset($admin) and $admin)    
                    <span>{{ $order->email }}</span>
                    <span>{{ $order->phone_number }}</span>
                @endif
            </strong>
        </p>

        <p>Thank you for your order. Below are the details of your order:</p>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->products as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->order_quantity }}</td>
                        <td>${{ number_format($item->price * $item->order_quantity, 2) }}</td>
                    </tr>
                @endforeach

                
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td>
                        <b>Total Quantity:</b>
                        <span>${{ number_format($order->total_quantity, 2) }}</span>
                    </td>

                    <td>
                        <b colspan="">Total Price:</b>
                        <span>${{ number_format($order->total_price, 2) }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>
        @if(isset($admin) and $admin)
            
        @else
            <p>Thank you for shopping with us.</p>
        @endif
        

        <p>Regards,<br>
            Rapid Crew Tech</p>

        @if (isset($link) and !empty($link))
            <div class="bottom-section">
                <p>Click the link below to view your previous orders on our website: <br> <a href={{$link}}>{{$link}}</a> </p>
                
            </div>
        @endif
    </div>
</body>
</html>
