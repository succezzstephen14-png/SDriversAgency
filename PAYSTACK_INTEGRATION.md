# Paystack Payment Integration - Driver Registration

## Overview
This document describes the Paystack payment integration for the driver registration feature. Users must complete a payment before their driver profile appears on the platform.

## Payment Flow

1. **User Registration Form**: User fills out driver profile information
2. **Payment Initiation**: Form submits to payment controller which:
   - Validates driver data
   - Creates a pending Payment record in the database
   - Displays Paystack payment form
3. **Paystack Payment**: User completes payment on Paystack
4. **Payment Verification**: 
   - User is redirected back to app with reference code
   - App verifies payment with Paystack API
   - On success, Driver profile is created and becomes visible
5. **Driver Profile Goes Live**: Driver appears on the Drivers listing page

## Setup Instructions

### 1. Get Paystack Credentials

- Go to [Paystack Dashboard](https://dashboard.paystack.com)
- Create an account and get your API keys:
  - **Public Key**: Found in Settings > API Keys (for frontend)
  - **Secret Key**: Found in Settings > API Keys (for backend verification)

### 2. Configure Environment Variables

Configure the matching Paystack public and secret keys in the server environment. For production,
use the live keys in Hostinger's environment settings or the production `.env` file outside the
public web root. Never commit real keys to GitHub. After changing them, clear Laravel's cached
configuration with `php artisan config:clear`.

```env
PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
```

The public key is used by the checkout page; the secret key is used by the server to verify each
transaction. Both must belong to the same Paystack mode (test or live). Do not mix a live public
key with a test secret key, or vice versa.

### 3. Development/testing

For development, use Paystack test keys and test payment methods from Paystack's documentation.
Use live keys only for real customer transactions.

### 4. Database Migration

The payments table is created automatically when running migrations:

```bash
php artisan migrate
```

This creates the `payments` table to track all payment transactions.

## Files Created/Modified

### New Files:
- `app/Http/Controllers/PaymentController.php` - Handles payment logic
- `app/Models/Payment.php` - Payment model
- `resources/views/payments/paystack-form.blade.php` - Payment form view
- `database/migrations/2026_04_28_000000_create_payments_table.php` - Payments table

### Modified Files:
- `routes/web.php` - Added payment routes
- `config/services.php` - Added Paystack configuration
- `.env` - Added Paystack keys
- `resources/views/drivers/create.blade.php` - Updated to redirect to payment

## Routes

### Payment Routes (Authenticated Users)

| Method | Route | Name | Purpose |
|--------|-------|------|---------|
| POST | `/drivers/payment/initiate` | `driver.payment.initiate` | Initiate driver payment |
| GET | `/payment/success` | `payment.success` | Payment success callback |
| GET | `/payment/cancelled` | `payment.cancelled` | Payment cancellation handler |
| POST | `/payment/webhook` | `payment.webhook` | Paystack webhook verification |

## Payment Models & Database

### Payment Record Structure

```
id (Primary Key)
user_id (FK to users)
reference (unique transaction reference)
amount (payment amount in Naira)
type (enum: 'driver', 'vehicle', 'recruitment')
data (JSON - stores temporary driver/vehicle data)
status (enum: 'pending', 'completed', 'failed', 'cancelled')
created_at
updated_at
```

### Payment Status Flow

```
pending → (user pays) → completed → driver created and visible
              ↓
           cancelled (user cancels) → user returns to form
```

## Security Considerations

1. **Secret Key Storage**: Never commit the secret key to version control
2. **API Verification**: All payments are verified with Paystack API before creating profiles
3. **CSRF Protection**: All routes include CSRF token validation
4. **User Association**: Payments are linked to authenticated users

## Troubleshooting

### Payment Not Verifying
- Ensure API keys are correct in `.env`
- Check that the reference code matches
- Verify internet connection for API call

### Driver Not Created After Payment
- Check the Payment record status in database
- Review application logs for errors
- Ensure user is authenticated when accessing payment pages

### Paystack Form Not Showing
- Verify Paystack public key in `.env`
- Check browser console for JavaScript errors
- Ensure Paystack CDN is accessible

## Future Enhancements

1. Add webhook endpoint for real-time payment confirmations
2. Implement payment refund functionality
3. Add email notifications for successful payments
4. Support multiple currencies
5. Extend to vehicle and recruitment payments
6. Add payment history to user dashboard

## Support

For issues with:
- **Paystack Integration**: Check [Paystack Documentation](https://paystack.com/docs)
- **Laravel**: Check [Laravel Documentation](https://laravel.com/docs)
- **Application**: Review application logs at `storage/logs/`
