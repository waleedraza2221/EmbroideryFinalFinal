@extends('layouts.landing')

@section('title','Professional Embroidery Digitizing')

@section('content')
<div class="bg-white">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-6 py-20 lg:py-28">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-5xl font-extrabold leading-tight">Professional Embroidery Digitizing</h1>
                <p class="mt-6 text-lg md:text-xl text-indigo-50">Turn your images or ideas into ready-to-stitch embroidery files. Fast turnaround, all formats, and guaranteed quality.</p>
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <a href="#quote" class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-white text-indigo-700 font-semibold shadow hover:bg-indigo-50 transition">Get Quote</a>
                    <a href="#pricing" class="inline-flex items-center justify-center px-6 py-3 rounded-md border border-white/70 text-white font-semibold hover:bg-white/10 transition">View Pricing</a>
                </div>
            </div>
        </div>
        <div class="absolute inset-0 pointer-events-none hero-visual">
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-gradient-to-tr from-white/10 to-transparent blur-3xl"></div>
            <div class="hero-ring absolute bottom-0 left-1/2 -translate-x-1/2 w-[50rem] h-[50rem] border border-white/10 rounded-full opacity-40"></div>
            <div class="hero-ring absolute bottom-0 left-1/2 -translate-x-1/2 w-[35rem] h-[35rem] border border-white/10 rounded-full opacity-30"></div>
        </div>
        <style>
            /* Hero ring animation */
            .hero-visual .hero-ring { 
                --ring-angle: 120deg; /* default (overridden by JS) */
                --ring-scale: 1.05;   /* default (overridden by JS) */
                animation: ringDrift 42s ease-in-out infinite;
                will-change: transform, opacity;
                backdrop-filter: blur(0px);
            }
            @keyframes ringDrift {
                0% { transform: translate(-50%,0) scale(1) rotate(0deg); opacity: .30; }
                25% { transform: translate(calc(-50% + 14px), -14px) scale(1.015) rotate(calc(var(--ring-angle) * .35)); }
                50% { transform: translate(calc(-50% - 10px), -28px) scale(var(--ring-scale)) rotate(calc(var(--ring-angle) * .55)); opacity: .55; }
                75% { transform: translate(calc(-50% + 6px), -14px) scale(1.02) rotate(calc(var(--ring-angle) * .30)); }
                100% { transform: translate(-50%,0) scale(1) rotate(0deg); opacity: .30; }
            }
            @media (prefers-reduced-motion: reduce) { .hero-visual .hero-ring { animation: none !important; } }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const rings = document.querySelectorAll('.hero-ring');
                rings.forEach(r => {
                    const duration = 30 + Math.random() * 25; // 30s - 55s
                    const delay = -Math.random() * duration;   // start at random point
                    const angle = 45 + Math.random() * 210;    // 45deg - 255deg arc twist
                    const scale = 1.01 + Math.random() * 0.09; // 1.01 - 1.10
                    r.style.setProperty('--ring-angle', angle + 'deg');
                    r.style.setProperty('--ring-scale', scale.toString());
                    r.style.animationDuration = duration + 's';
                    r.style.animationDelay = delay + 's';
                    if (Math.random() > 0.5) r.style.animationDirection = 'reverse';
                });
            });
        </script>
    </section>

    <!-- How It Works -->
    <section class="py-16 bg-gray-50" id="how-it-works">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-gray-800 text-center">How It Works</h2>
            <p class="text-center text-gray-500 mt-2 max-w-2xl mx-auto">Get professional embroidery files in just three simple steps.</p>
            <div class="mt-12 grid md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold text-lg">01</div>
                    <h3 class="mt-4 font-semibold text-gray-800">Upload or Describe</h3>
                    <p class="mt-2 text-sm text-gray-500">Send your image, logo, or detailed idea and choose required formats.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold text-lg">02</div>
                    <h3 class="mt-4 font-semibold text-gray-800">AI + Digitizing</h3>
                    <p class="mt-2 text-sm text-gray-500">We convert it into optimized stitch data with expert quality checks.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-md transition">
                    <div class="w-12 h-12 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold text-lg">03</div>
                    <h3 class="mt-4 font-semibold text-gray-800">Receive Your Files</h3>
                    <p class="mt-2 text-sm text-gray-500">Download in DST, PES, EXP, JEF and more—ready for production.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Quote Form (simple, points to auth/register if guest) -->
    <section class="py-20" id="quote">
        <div class="max-w-5xl mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-10 items-start">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Get Your Custom Quote</h2>
                    <p class="mt-3 text-gray-500">Tell us about your project and receive a detailed quote within hours.</p>
                    <ul class="mt-6 space-y-2 text-sm text-gray-600 list-disc list-inside">
                        <li>Fast 2-4 hour quoting window</li>
                        <li>All major embroidery machine formats</li>
                        <li>Rush & complex design handling</li>
                        <li>Unlimited revisions guarantee</li>
                    </ul>
                </div>
                <div class="bg-white rounded-xl shadow p-6">
                    @guest
                        <p class="text-sm text-gray-600 mb-4">Please <a href="{{ route('login') }}" class="text-indigo-600 font-medium">login</a> or <a href="{{ route('register') }}" class="text-indigo-600 font-medium">register</a> to submit a quote request.</p>
                    @else
                        <form action="{{ route('quote-requests.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Design / Description</label>
                                <textarea name="description" rows="4" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Describe your design"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Upload File (optional)</label>
                                <input type="file" name="files[]" class="mt-1 w-full text-sm" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Format Needed</label>
                                    <input name="format" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="DST / PES / ..." />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Colors</label>
                                    <input name="colors" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="e.g. 5" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Deadline</label>
                                <input type="date" name="deadline" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Additional Notes (optional)</label>
                                <textarea name="notes" rows="3" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"></textarea>
                            </div>
                            <button class="w-full py-3 bg-indigo-600 text-white rounded-md font-semibold hover:bg-indigo-700 transition">Submit Quote Request</button>
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-gray-800 text-center">Simple, Transparent Pricing</h2>
            <p class="text-center text-gray-500 mt-2">Choose the plan that fits your design complexity.</p>
            <div class="mt-12 grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-xl shadow p-6 flex flex-col border border-indigo-100">
                    <h3 class="text-lg font-semibold text-gray-800">Basic</h3>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900">$15<span class="text-base font-medium text-gray-500">/design</span></p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600 flex-1">
                        <li>Up to 5,000 stitches</li>
                        <li>1-3 colors</li>
                        <li>48h delivery</li>
                        <li>DST format included</li>
                        <li>1 free revision</li>
                    </ul>
                    <a href="#quote" class="mt-6 inline-block w-full text-center py-2.5 rounded-md bg-indigo-50 text-indigo-700 font-medium hover:bg-indigo-100">Order Basic</a>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col border-2 border-indigo-500 relative">
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-semibold px-3 py-1 rounded-full">Most Popular</span>
                    <h3 class="text-lg font-semibold text-gray-800">Standard</h3>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900">$25<span class="text-base font-medium text-gray-500">/design</span></p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600 flex-1">
                        <li>Up to 15,000 stitches</li>
                        <li>Up to 6 colors</li>
                        <li>24h delivery</li>
                        <li>All common formats</li>
                        <li>2 free revisions</li>
                        <li>Color matching</li>
                    </ul>
                    <a href="#quote" class="mt-6 inline-block w-full text-center py-2.5 rounded-md bg-indigo-600 text-white font-medium hover:bg-indigo-700">Order Standard</a>
                </div>
                <div class="bg-white rounded-xl shadow p-6 flex flex-col border border-indigo-100">
                    <h3 class="text-lg font-semibold text-gray-800">Premium</h3>
                    <p class="mt-2 text-3xl font-extrabold text-gray-900">$45<span class="text-base font-medium text-gray-500">/design</span></p>
                    <ul class="mt-4 space-y-2 text-sm text-gray-600 flex-1">
                        <li>Unlimited stitches</li>
                        <li>Unlimited colors</li>
                        <li>12h rush option</li>
                        <li>All formats + extras</li>
                        <li>Unlimited revisions</li>
                        <li>Priority support</li>
                        <li>3D mockup included</li>
                    </ul>
                    <a href="#quote" class="mt-6 inline-block w-full text-center py-2.5 rounded-md bg-indigo-50 text-indigo-700 font-medium hover:bg-indigo-100">Order Premium</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio / Sample Designs -->
    <section id="portfolio" class="py-20">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-gray-800 text-center">Sample Designs & Portfolio</h2>
            <p class="text-center text-gray-500 mt-2 max-w-2xl mx-auto">Explore styles we routinely digitize – optimized for clarity, density & machine efficiency.</p>
            <div class="mt-12 grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach([
                    ['title'=>'Logo Embroidery','tag'=>'Corporate Branding','img'=>'https://via.placeholder.com/600x420?text=Logo'],
                    ['title'=>'Floral Design','tag'=>'Decorative Art','img'=>'https://via.placeholder.com/600x420?text=Floral'],
                    ['title'=>'Modern Pattern','tag'=>'Contemporary','img'=>'https://via.placeholder.com/600x420?text=Pattern'],
                ] as $card)
                <div class="group bg-white rounded-xl shadow hover:shadow-lg transition overflow-hidden border border-gray-100">
                    <div class="aspect-[4/3] bg-gray-100 flex items-center justify-center text-gray-400 text-sm font-medium">
                        <img src="{{ $card['img'] }}" alt="{{ $card['title'] }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition"/>
                    </div>
                    <div class="p-5">
                        <p class="text-xs uppercase tracking-wide text-indigo-600 font-semibold">{{ $card['tag'] }}</p>
                        <h3 class="mt-2 font-semibold text-gray-800">{{ $card['title'] }}</h3>
                        <a href="#quote" class="mt-3 inline-flex items-center text-sm text-indigo-600 font-medium hover:underline">View Details →</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Why Choose Us / Features -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-gray-800 text-center">Why Choose Our Service?</h2>
            <p class="text-center text-gray-500 mt-2 max-w-2xl mx-auto">Professional digitizing with an obsessive focus on stitch efficiency & visual fidelity.</p>
            <div class="mt-12 grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach([
                    ['h'=>'All File Formats','p'=>'DST, PES, EXP, JEF, VP3, HUS & more – multi-platform ready.'],
                    ['h'=>'Fast Turnaround','p'=>'Standard 24-48h, rush options as low as 6-12h.'],
                    ['h'=>'Custom Colors','p'=>'Thread & brand color matching for consistent production.'],
                    ['h'=>'Quality Guarantee','p'=>'Free revisions until perfect — production-ready or we fix it.'],
                ] as $f)
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-md bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">★</div>
                    <h3 class="mt-4 font-semibold text-gray-800">{{ $f['h'] }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ $f['p'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- File Formats & Stats -->
    <section class="py-14">
        <div class="max-w-6xl mx-auto px-6">
            <div class="grid lg:grid-cols-2 gap-10 items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Supported File Formats</h2>
                    <p class="mt-3 text-gray-500 text-sm">We deliver machine-optimized stitch files across all major ecosystems.</p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-medium">
                        @foreach(['DST','PES','EXP','JEF','VP3','HUS','XXX','VIP'] as $fmt)
                            <span class="px-3 py-1 rounded-full bg-indigo-50 text-indigo-700">{{ $fmt }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-6">
                    @foreach([
                        ['n'=>'5,000+','l'=>'Happy Customers'],
                        ['n'=>'15,000+','l'=>'Designs Created'],
                        ['n'=>'24hr','l'=>'Avg. Delivery'],
                    ] as $s)
                    <div class="bg-white rounded-lg border border-gray-100 p-6 text-center shadow-sm">
                        <p class="text-2xl font-extrabold text-indigo-600">{{ $s['n'] }}</p>
                        <p class="mt-1 text-xs font-medium tracking-wide text-gray-500 uppercase">{{ $s['l'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials (dynamic) -->
    <section id="testimonials" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-gray-800 text-center">What Our Customers Say</h2>
            <p class="text-center text-gray-500 mt-2 max-w-2xl mx-auto">Consistent quality & dependable delivery build long-term trust.</p>
            <div class="mt-12 grid md:grid-cols-3 gap-8">
                @forelse(($testimonials ?? collect()) as $t)
                    <div class="bg-white p-6 rounded-xl shadow border border-gray-100 relative">
                        <span class="absolute -top-5 left-6 text-5xl text-indigo-200 select-none">“</span>
                        <p class="mt-2 text-sm text-gray-600">{{ $t->quote }}</p>
                        <div class="mt-4 flex items-center gap-3">
                            @if($t->avatar)
                                <img src="{{ $t->avatar }}" class="w-10 h-10 rounded-full object-cover" alt="{{ $t->name }}"/>
                            @else
                                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold">{{ strtoupper(substr($t->name,0,1)) }}</div>
                            @endif
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $t->name }}</p>
                                <p class="text-xs text-gray-500">{{ $t->role }} @if($t->company) • {{ $t->company }} @endif</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-center text-sm text-gray-500">Testimonials coming soon.</p>
                @endforelse
            </div>
        </div>
    </section>

    <!-- FAQ (Alpine accordion) -->
    <section id="faq" class="py-20">
        <div class="max-w-4xl mx-auto px-6">
            <h2 class="text-3xl font-bold text-gray-800 text-center">Frequently Asked Questions</h2>
            <p class="text-center text-gray-500 mt-2">Still have questions? Reach out any time.</p>
            <div class="mt-10 space-y-4" x-data="{open:null}">
                @foreach([
                    ['q'=>'What input formats do you accept?','a'=>'Common raster (PNG, JPG) & vector (AI, EPS, PDF, SVG). High-res yields best accuracy.'],
                    ['q'=>'How long does it take?','a'=>'Standard orders 24-48h. Rush options 6-12h depending on complexity.'],
                    ['q'=>'Do you offer revisions?','a'=>'Unlimited revisions until the stitch file runs clean & matches your expectations.'],
                    ['q'=>'Can you match brand colors?','a'=>'Yes, provide Pantone / thread chart numbers; we map closest machine library tone.'],
                ] as $i => $faq)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800" x-data="{id:{{ $i }}}">
                    <button @click="open===id?open=null:open=id" class="w-full flex justify-between items-center px-5 py-4 text-left group">
                        <span class="font-medium text-gray-800 dark:text-gray-100 text-sm md:text-base">{{ $faq['q'] }}</span>
                        <span class="ml-4 inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-50 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 transition-transform duration-300" :class="open===id ? 'rotate-180' : 'rotate-0'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </span>
                    </button>
                    <div x-show="open===id" x-collapse class="px-5 pb-5 text-sm text-gray-600 dark:text-gray-300">{{ $faq['a'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Newsletter / Lead Capture -->
    <section class="py-16 bg-indigo-600 text-white">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold">Get a Free Sample Design</h2>
            <p class="mt-3 text-indigo-100">Subscribe for tips, formats & exclusive offers. Unsubscribe anytime.</p>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                @csrf
                <input type="email" name="email" required placeholder="Your email" class="w-full sm:w-2/3 px-4 py-3 rounded-md text-gray-800 focus:ring-2 focus:ring-indigo-300 outline-none"/>
                <button class="px-6 py-3 rounded-md bg-white text-indigo-700 font-semibold hover:bg-indigo-50">Subscribe</button>
            </form>
            <form action="{{ route('newsletter.unsubscribe') }}" method="POST" class="mt-4 text-xs text-indigo-200 flex flex-col sm:flex-row gap-2 justify-center items-center">
                @csrf
                <input type="email" name="email" placeholder="Enter email to unsubscribe" class="px-3 py-2 rounded-md text-gray-800 focus:ring-2 focus:ring-indigo-300 outline-none"/>
                <button class="px-4 py-2 rounded-md bg-indigo-500 hover:bg-indigo-400 text-white font-medium">Unsubscribe</button>
            </form>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-indigo-600 text-white">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold">Ready to Start Your Project?</h2>
            <p class="mt-3 text-indigo-100">Create an account or sign in to submit your first embroidery digitizing request now.</p>
            <div class="mt-6 flex flex-col sm:flex-row gap-4 justify-center">
                @guest
                    <a href="{{ route('register') }}" class="px-6 py-3 rounded-md bg-white text-indigo-700 font-semibold hover:bg-indigo-50">Get Started</a>
                    <a href="{{ route('login') }}" class="px-6 py-3 rounded-md border border-white/70 font-semibold hover:bg-white/10">Login</a>
                @else
                    <a href="{{ route('quote-requests.create') }}" class="px-6 py-3 rounded-md bg-white text-indigo-700 font-semibold hover:bg-indigo-50">New Quote Request</a>
                @endguest
            </div>
        </div>
    </section>
</div>
@endsection
