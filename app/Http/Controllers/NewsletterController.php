<?php
namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Mail\NewsletterConfirm;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'email'=>'required|email:rfc,dns|max:255'
        ]);

        // Simple rate limit: 5 per hour per IP
        $key = 'nl_subscribe:'.sha1($request->ip());
        $attempts = Cache::get($key, 0);
        if($attempts >= 5){
            return back()->withErrors(['email'=>'Too many attempts. Try again later.']);
        }
        Cache::put($key, $attempts+1, now()->addHour());

        $sub = NewsletterSubscription::updateOrCreate(
            ['email'=>$data['email']],
            [
                'ip'=>$request->ip(),
                'user_agent'=>$request->userAgent(),
                'is_active'=>true,
                'unsubscribed_at'=>null,
            ]
        );

        if(!$sub->confirmed_at){
            $sub->confirmation_token = Str::random(40);
            $sub->save();
            Mail::to($sub->email)->send(new NewsletterConfirm($sub));
            return back()->with('success','Check your email to confirm subscription.');
        }

        return back()->with('success','You are already subscribed.');
    }

    public function unsubscribe(Request $request)
    {
        $request->validate(['email'=>'required|email']);
        $sub = NewsletterSubscription::where('email',$request->input('email'))->first();
        if($sub){
            $sub->update(['is_active'=>false,'unsubscribed_at'=>now()]);
        }
        return back()->with('success','You have been unsubscribed.');
    }

    public function confirm(string $token)
    {
        $sub = NewsletterSubscription::where('confirmation_token',$token)->firstOrFail();
        $sub->markConfirmed();
        return redirect()->route('home')->with('success','Subscription confirmed!');
    }
}
