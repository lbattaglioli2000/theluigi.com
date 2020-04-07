---
title: 'The Chocolate Milk Mile'
publish_date: '2020-03-20 16:13'
author: 1
summary: 'I recently built a web application to handle event registration for The Chocolate Milk Mile. Join me in uncovering the inner-workings of this application.'
article_body:
  -
    type: heading
    attrs:
      level: 1
    content:
      -
        type: text
        text: 'What is The Chocolate Milk Mile?'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'To quote from their fancy new website ('
      -
        type: text
        marks:
          -
            type: link
            attrs:
              href: 'https://www.thechocolatemilkmile.org'
              target: _blank
              rel: null
        text: thechocolatemilkmile.org
      -
        type: text
        text: )
  -
    type: set
    attrs:
      values:
        type: quote
        quote: |
          The Chocolate Milk Mile is an event dedicated to celebrating the life of Noah Farrelly, who loved running. All of the proceeds from this event will be donated to the Noah Farrelly Memorial Fund. 
          
          The event entails that participants drink a glass of chocolate milk upon completion of every lap of a mile. This adds up to be about a half gallon of chocolate milk. 
          
          Not only is it a great cause, but it's an awesome time for the community to get together and have some fun.
          
        author: 'Nick Hunzicker'
        cite: 'https://thechocolatemilkmile.org'
        source: 'The Chocolate Milk Mile'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'One of the event directors, Nick Hunzicker, is a good friend of mine as well and he was curious about learning how to build web apps. I couldn''t say no to a chance to teach what I know, while also helping out a good cause. Name a more perfect duo, I''ll wait. Just kidding, I''ll move on now. '
  -
    type: paragraph
    content:
      -
        type: text
        text: 'When Nick came to me, he originally wanted a landing page for the event. A place for people to go so they can find out more information about the event, what it''s purpose is, how much it is, etc. Once the landing pages were built, he wanted to build a custom registration system for people to use to register for the event.. obviously. '
  -
    type: paragraph
    content:
      -
        type: text
        text: 'On the backend, he wanted an event manager dashboard where he could get an overview of how many registrants he has signed up to run, how much money the event has brought in, and a place to check-in the registrants on the day of the event.'
  -
    type: set
    attrs:
      values:
        type: attention_box
        alert_type: nerd_alert
        message: 'I''m gonna nerd out for a bit and talk about how this application works. There will be a hefty ammount of code in here. If that''s not your kinda thing, check out my other articles! I have plenty of non-code related things!'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Inspired by Ron Swanson''s '
      -
        type: text
        marks:
          -
            type: italic
        text: 'Very Good Building and Development Co.'
      -
        type: text
        text: ', we decided to build our own '
      -
        type: text
        marks:
          -
            type: italic
        text: 'Very Good Registration Systems, Inc'
      -
        type: text
        text: '. Use it, or don''t. End of tagline.'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'When we were building it, we wanted a really minimal design, that was easy to use, and it had to be really efficient when it came to checking people in on the day of the event. We decided to put in the effort and make use of real-time event broadcasting to make (almost) the whole system always in sync. This was a big learning opportunity for me because I hadn''t used real time events in the past, so this was cool to play with.'
  -
    type: heading
    attrs:
      level: 2
    content:
      -
        type: text
        text: 'Registration Process'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'After creating an account in the system, the registration process is pretty straight forward. The user simply selects the event they want to register for. It then shows them the registration form.'
  -
    type: set
    attrs:
      values:
        type: photo
        photo: Screen-Shot-2020-03-20-at-1.58.22-PM.png
        caption: 'Pretty straightforward, right?'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Now, let''s take a quick peek at the controller to see what''s goin'' on behind the scenes when a user registers for the event.'
  -
    type: paragraph
  -
    type: set
    attrs:
      values:
        type: php_code
        php_code: |
          public function post(Event $event){
              $this->validate(request(), [
                  'name' => 'required|string',
                  'email' => 'required|email|unique:registrations',
                  'payment_token' => 'required|string',
                  'shirtSize' => 'required_if:hasShirt,true',
              ]);
          
              $registration = $event->register([
                  'name' => request('name'),
                  'email' => request('email'),
                  'mile_time' => request('mile_time'),
                  'user_id' => Auth::user()->id,
              ]);
          
              if(request()->has('hasShirt') && request()->has('shirtSize')){
                  $registration->orderShirt(request('shirtSize'));
              }
          
              try {
                  $this->paymentGateway->charge($registration, request('payment_token'));
              } catch (PaymentFailedException $exception) {
                  $registration->cancel();
                  return back()->with('payment_error', 'Uh-oh! Your payment failed. Try again? If that fails, contact us!');
              }
              
              return redirect(route('registration.confirmation', [$event, $registration]));
              
          }
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Using the magic of Laravel''s route-model binding, the controller accepts whatever '
      -
        type: text
        marks:
          -
            type: code
        text: Event
      -
        type: text
        text: ' that the user is registering for. We do some simple validation of the request, ensuring that they gave us a name, an email, filled in a credit card number, and that they gave us a shirt size if their order contains a t-shirt.'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'From there, we call the '
      -
        type: text
        marks:
          -
            type: code
        text: register
      -
        type: text
        text: ' method on the bound '
      -
        type: text
        marks:
          -
            type: code
        text: Event
      -
        type: text
        text: '. If we take a look at that method, it takes in an array representing the registrant. '
  -
    type: set
    attrs:
      values:
        type: php_code
        php_code: |
          public function register($registrant){
              return $this->registrations()->create([
                  'user_id' => $registrant['user_id'],
                  'name' => $registrant['name'],
                  'mile_time' => $registrant['mile_time'],
                  'email' => $registrant['email'],
              ]);
          }
  -
    type: paragraph
    content:
      -
        type: text
        text: 'This method simply creates a new '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: ' associated with this '
      -
        type: text
        marks:
          -
            type: code
        text: Event
      -
        type: text
        text: ', and returns that newly created '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: .
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Back in the controller, we go on to check if the request has a shirt order with it. The request will have a '
      -
        type: text
        marks:
          -
            type: code
        text: hasShirt
      -
        type: text
        text: ' key if the registrant wants a shirt with their order. If they do in fact want a shirt with their registration, we then call the '
      -
        type: text
        marks:
          -
            type: code
        text: orderShirt
      -
        type: text
        text: ' method on the '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: .
  -
    type: set
    attrs:
      values:
        type: php_code
        php_code: |
          public function orderShirt($size){
              $this->update(['hasShirt' => true]);
          
              return $this->shirtOrder()->create([
                  'size' => $size
              ]);
          }
  -
    type: paragraph
    content:
      -
        type: text
        text: 'All that this method is doing is setting the '
      -
        type: text
        marks:
          -
            type: code
        text: hasShirt
      -
        type: text
        text: ' property of the the '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: ' to '
      -
        type: text
        marks:
          -
            type: code
        text: 'true'
      -
        type: text
        text: ', and creating a new '
      -
        type: text
        marks:
          -
            type: code
        text: ShirtOrder
      -
        type: text
        text: ' for this '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: '. This method is also providing the new '
      -
        type: text
        marks:
          -
            type: code
        text: ShirtOrder
      -
        type: text
        text: ' with the size of the shirt that the user wants.'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'At this point we can use the '
      -
        type: text
        marks:
          -
            type: code
        text: PaymentGateway
      -
        type: text
        text: ' to charge the user for the registration. The '
      -
        type: text
        marks:
          -
            type: code
        text: charge
      -
        type: text
        text: ' method on the '
      -
        type: text
        marks:
          -
            type: code
        text: PaymentGateway
      -
        type: text
        text: ' accepts the '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: ' and a payment token from the front end (which is generated by Stripe). We can take a quick peek at that if you''d like:'
  -
    type: paragraph
  -
    type: set
    attrs:
      values:
        type: php_code
        php_code: |
          class StripePaymentGateway implements PaymentGateway
          {
              private $totalCharges;
          
              public function _construct(){
                  Stripe::setApiKey(env('STRIPE_SECRET'));
                  $this->totalCharges = collect();
              }
          
              public function charge(Registration $registration, $token){
          
                  $this->totalCharges->add($registration->price);
                  
                  // I was on a time crunch here... Probably not the best approach. We love magic numbers.
                  if($registration->hasShirtOrder()){
                      $this->totalCharges->add(1300);
                  }
          
                  try {
                      \Stripe\Charge::create([
                          'amount' => $this->totalCharges->sum(),
                          'currency' => 'usd',
                          'description' => 'Event Registration Fee',
                          'source' => $token,
                      ]);
                  }
                  catch (CardException $exception)
                  {
                      $registration->cancel();
                      throw new PaymentFailedException($exception);
                  }
                  catch (ApiErrorException $apiErrorException){
                      $registration->cancel();
                      throw new PaymentFailedException($apiErrorException);
                  }
          
                  return $registration->confirm();
          
              }
          }
  -
    type: paragraph
    content:
      -
        type: text
        text: 'If the '
      -
        type: text
        marks:
          -
            type: code
        text: PaymentGateway
      -
        type: text
        text: ' throws a '
      -
        type: text
        marks:
          -
            type: code
        text: PaymentFailedException
      -
        type: text
        text: ', we cancel the registration, and redirect back to the form letting the user know their payment method failed. If the charge goes through successfully, we call the '
      -
        type: text
        marks:
          -
            type: code
        text: confirm
      -
        type: text
        text: ' method on the '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: ' model.'
  -
    type: set
    attrs:
      values:
        type: php_code
        php_code: |
          public function confirm(){
              $this->update([
                  'confirmed_at' => Carbon::now(),
                  'confirmation_number' => ConfirmationIssuer::issueConfirmationNumber()
              ]);
          }
  -
    type: paragraph
    content:
      -
        type: text
        text: 'All that '
      -
        type: text
        marks:
          -
            type: code
        text: confirm
      -
        type: text
        text: ' does is set the '
      -
        type: text
        marks:
          -
            type: code
        text: confirmed_at
      -
        type: text
        text: ' property to be the current date and time, and then it uses the '
      -
        type: text
        marks:
          -
            type: code
        text: ConfirmationIssuer
      -
        type: text
        text: ' to issue a new confirmation number to the '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: .
  -
    type: paragraph
    content:
      -
        type: text
        text: 'All that''s left is to return a redirect to the confirmation page, passing the '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: ' along with it and displaying the finalized information to the registrant. It''ll display their confirmation number and some other information about their registration.'
  -
    type: set
    attrs:
      values:
        type: photo
        photo: Screen-Shot-2020-03-20-at-4.24.58-PM.png
        caption: 'This is that confirmation page I was telling ya about!'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Awesome! Now we''ve got a new registration in the system! If we were to click that blue button to go to our registrations, we''d see that newly created registration, along with a QR code. '
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Let''s switch gears a bit and talk about the backend event management system.'
  -
    type: heading
    attrs:
      level: 1
    content:
      -
        type: text
        text: 'The Check In Process'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'The process of registering for the event is a '
      -
        type: text
        marks:
          -
            type: bold
        text: 'piece of cake'
      -
        type: text
        text: '. We wanted to make checking in on the day of the event a '
      -
        type: text
        marks:
          -
            type: bold
        text: 'whole cake'
      -
        type: text
        text: ' in and of itself. That''s why we adopted the use of QR codes. '
  -
    type: paragraph
    content:
      -
        type: text
        text: 'The QR code encodes the confirmation number associated with the registration. This allows the event managers who are checking people in, to simply scan the code to access the registrant''s information within the system.'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Within the manager dashboard, we have a check in page. On that page, there''s a '
      -
        type: text
        marks:
          -
            type: code
        text: VueQrcode
      -
        type: text
        text: ' component from '
      -
        type: text
        marks:
          -
            type: link
            attrs:
              href: 'https://github.com/fengyuanchen/vue-qrcode'
              target: _blank
              rel: null
        text: 'this cool library'
      -
        type: text
        text: ' I stumbled upon on GitHub. This component detects when there''s a QR code within the view of the webcam. When it detects one, it''ll call the '
      -
        type: text
        marks:
          -
            type: code
        text: onDecode
      -
        type: text
        text: ' method. Let''s take a peek at that real quick:'
  -
    type: set
    attrs:
      values:
        type: javascript_code
        javascript_code: |
          onDecode (confirmationNumber) {
              this.confirmationnumber = confirmationNumber;
              axios
                  .get('/api/confirmation/' + this.confirmationnumber)
                  .then(response => {
                      this.event = response.data.event;
                      this.registration = response.data.registration;
                  })
                  .catch(error => {
          
                      alert('That is not a valid confirmation number. Please try again.');
          
                  });
          },
  -
    type: paragraph
    content:
      -
        type: text
        text: 'The parameter '
      -
        type: text
        marks:
          -
            type: code
        text: confirmationNumber
      -
        type: text
        text: ', contains the value decoded from the QR code. We then go ahead and save that confirmation number to the Vue component. '
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Finally, once we have the confirmation number from the registrant''s QR code, we make a post request to our backend to check-in the registrant with that confirmation number. On the server, that looks like this:'
  -
    type: set
    attrs:
      values:
        type: php_code
        php_code: |
          Route::post('/manager/check-in', function (Request $request){
              try {
                  $registration = App\Registration::where('confirmation_number', $request->confirmation_number)->firstOrFail()->checkIn();
                  $event = $registration->event;
          
                  event(new App\Events\RegistrantCheckedIn($registration));
          
                  return [
                      'registration' => $registration,
                      'event' => $event
                  ];
              } catch (\App\Exceptions\AlreadyCheckedInException $exception){
                  return response([
                      'error' => 'You are already checked in.',
                  ], 422);
              }
          });
  -
    type: paragraph
    content:
      -
        type: text
        text: 'We first try to find the registration with the matching confirmation number. Because we''re using the '
      -
        type: text
        marks:
          -
            type: code
        text: firstOrFail
      -
        type: text
        text: ' method, this will return a 404 if it can''t find a registration. Once we do have the '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: ' however, we call the '
      -
        type: text
        marks:
          -
            type: code
        text: checkIn
      -
        type: text
        text: ' method on it.'
  -
    type: set
    attrs:
      values:
        type: php_code
        php_code: |
          public function checkIn(){
              if($this->checked_in_at == null){
                  $this->update([
                      'checked_in_at' => Carbon::now()
                  ]);
                  return $this;
              }
              throw new AlreadyCheckedInException();
          }
  -
    type: paragraph
    content:
      -
        type: text
        text: 'The '
      -
        type: text
        marks:
          -
            type: code
        text: checkIn
      -
        type: text
        text: ' method simply sets the '
      -
        type: text
        marks:
          -
            type: code
        text: checked_in_at
      -
        type: text
        text: ' property of the '
      -
        type: text
        marks:
          -
            type: code
        text: Registration
      -
        type: text
        text: ' to be the current timestamp. It''ll also check if the person is already checked in, in which case it will throw an '
      -
        type: text
        marks:
          -
            type: code
        text: AlreadyCheckedInException
      -
        type: text
        text: ' and will let the manager know they''re already checked in. '
  -
    type: paragraph
    content:
      -
        type: text
        text: 'After we check-in the the registrant, we fire off a new '
      -
        type: text
        marks:
          -
            type: code
        text: RegistrantCheckedIn
      -
        type: text
        text: ' event. This immediately broadcasts an event to Pusher, saying that the registrant was checked in. We can then pickup that broadcasted event on the front end using Laravel Echo, and we can then update the users registration to show they''re checked in. On the user''s end, we have a couple methods in our Vue component to do this.'
  -
    type: set
    attrs:
      values:
        type: javascript_code
        javascript_code: |
          methods: {
              getRegistrations() {
                  axios
                      .get('/user/api/registrations')
                      .then(response => {
                          this.registrations = response.data;
                      });
              },
          
              listen() {
                  window.Echo.channel('registrations')
                      .listen('RegistrantCheckedIn', (e) => {
                          this.getRegistrations();
                      });
              }
          },
          
          mounted() {
              this.getRegistrations();
              this.listen();
          },
  -
    type: paragraph
    content:
      -
        type: text
        text: 'When the page is mounted, we get the user''s current registrations and display them. We then call the '
      -
        type: text
        marks:
          -
            type: code
        text: listen
      -
        type: text
        text: ' method which triggers Laravel Echo to listen for the '
      -
        type: text
        marks:
          -
            type: code
        text: RegistrantCheckedIn
      -
        type: text
        text: ' event within the '
      -
        type: text
        marks:
          -
            type: code
        text: registrations
      -
        type: text
        text: ' Pusher channel. When it picks up on a new registrant being checked in, it''ll call '
      -
        type: text
        marks:
          -
            type: code
        text: getRegistrations
      -
        type: text
        text: ' again, and refresh our registration information.'
  -
    type: heading
    attrs:
      level: 1
    content:
      -
        type: text
        text: 'Wow, you''re still here?!'
  -
    type: paragraph
    content:
      -
        type: text
        text: 'Wow. I applaud your commitment, and I appreciate you taking the time to read this. I hope you found it as interesting as I did. I had a blast building this with my good friend Nick, and if you''re reading this, I hope you learned a little something too. And on the off chance you '
      -
        type: text
        marks:
          -
            type: bold
        text: 'didn''t'
      -
        type: text
        text: ' learn anything, maybe this article will help to demystify the codebase for you. 😆'
updated_by: 1
updated_at: 1586282781
article_photo: puke.png
id: c701f323-2042-4d89-991f-2f653834920d
---
