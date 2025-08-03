# 2Checkout Payment Flow Integration Guide

## 🔄 **Complete Payment Flow**

### 1. **Customer Initiates Payment**
- Customer clicks "Accept Quote & Pay" button on quote request page
- Direct href link generated using `PaymentController::generatePaymentURL()`
- Customer redirected to 2Checkout ConvertPlus page

### 2. **2Checkout Processing**  
- Customer completes payment on 2Checkout secure page
- 2Checkout processes the payment
- Upon completion, customer is redirected to: `https://embroiderydigitize.com/thank-you/`

### 3. **Return URL Handling**
- **Production**: 2Checkout redirects to `https://embroiderydigitize.com/thank-you/` 
- **Development**: JavaScript automatically redirects to `http://localhost:8000/thank-you/` with parameters
- Parameters include: `order_number`, `invoice_id`, `merchant_order_id`, `total`, `key`

### 4. **Thank You Page Display**
- Shows payment confirmation with details
- Displays processing message
- Auto-redirects to dashboard after 10 seconds (for logged-in users)
- Logs payment data for debugging

### 5. **Webhook Verification** 
- 2Checkout sends webhook to `/payments/webhook` 
- PaymentController verifies webhook signature
- Creates/updates Payment record in database
- Updates QuoteRequest status to 'accepted'
- Creates Order record automatically

### 6. **Order Creation**
- Only happens after webhook verification
- Generates unique order number
- Associates with quote request and payment
- Sets delivery date based on quoted days
- Preserves all original project files

## 🔧 **Key Configuration**

### PaymentController Updates:
```php
// Return URL now points to production domain
'return_url' => 'https://embroiderydigitize.com/thank-you/'

// New thankYou() method handles return parameters
public function thankYou(Request $request)
```

### Routes Added:
```php
// Public route for thank you page (no auth required)
Route::get('/thank-you', [PaymentController::class, 'thankYou'])->name('payment.thank-you');
```

### Thank You Page Features:
- ✅ Displays payment confirmation details
- ✅ Shows processing status with spinner
- ✅ Auto-redirects for better UX
- ✅ Handles both success and error states
- ✅ Development redirect to localhost
- ✅ Console logging for debugging

## 🛡️ **Security & Verification**

### Webhook Security:
- HMAC SHA512 signature verification
- INS (Instant Notification Service) support
- Multiple payment identifier matching
- Comprehensive error logging

### Payment Verification:
- Webhook-based order creation (secure)
- Return URL for user experience only
- Double verification prevents fraud
- Status tracking throughout process

## 📱 **User Experience Flow**

1. **Quote Page** → Direct payment link
2. **2Checkout** → Secure payment processing  
3. **Thank You Page** → Immediate confirmation
4. **Webhook Processing** → Background order creation
5. **Dashboard** → Order appears in user account
6. **Email Confirmation** → Order details sent to customer

## 🔍 **Testing & Debugging**

### Development Testing:
1. Use ngrok tunnel for webhook testing
2. Return URL redirects to localhost automatically
3. Check browser console for payment parameters
4. Monitor Laravel logs for webhook processing

### Production Verification:
1. Verify return URL: `https://embroiderydigitize.com/thank-you/`
2. Confirm webhook URL: `https://embroiderydigitize.com/payments/webhook`
3. Test with 2Checkout sandbox mode first
4. Switch to live mode: `TWOCHECKOUT_SANDBOX=false`

## 🎯 **Implementation Benefits**

- ✅ **Clean Architecture**: Direct href links, no complex JavaScript
- ✅ **Secure Processing**: Webhook-based order creation
- ✅ **Better UX**: Immediate user feedback via thank you page
- ✅ **Production Ready**: Proper domain handling for both dev and prod
- ✅ **Reliable**: Webhook verification ensures no missed payments
- ✅ **Maintainable**: Simple, straightforward payment flow

## 🔄 **Flow Diagram**

```
Customer → Quote Page → 2Checkout → Thank You Page
                                        ↓
                     Webhook → Order Creation → Email Confirmation
```

This implementation provides a robust, secure, and user-friendly payment experience that works seamlessly in both development and production environments.
