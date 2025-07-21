<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2Checkout Locked Cart Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 50px auto; 
            padding: 20px; 
        }
        .test-button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
        }
        .test-button:hover {
            background: #45a049;
        }
        .test-button:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        .log {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <h1>2Checkout Locked Cart Test</h1>
    
    <div>
        <h2>Configuration</h2>
        <ul>
            <li><strong>Account Number:</strong> 255036765830</li>
            <li><strong>Method:</strong> Locked Cart with Signature</li>
            <li><strong>Sample Product Code:</strong> 74B8E17CC0</li>
        </ul>
    </div>

    <div>
        <h2>Tests</h2>
     <a href="https://secure.2checkout.com/checkout/buy?merchant=255036765830&currency=USD&tpl=default&dynamic=1&prod=eMBROIDERY+dIGITIZE&price=10&type=digital&qty=1&signature=8d79d543842d830820b0fd5f2e08f5570adfe9c70c0487ff92782b113581dd23" class="btn btn-success" id="buy-button">Buy now!</a>
    </div>

    <div id="log" class="log"></div>

   
</body>
</html>
