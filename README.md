Opauth-Timely
=============
[Opauth][1] strategy for Timely authentication.

Implemented based on https://dev.timelyapp.com/

Getting started
----------------
1. Install Opauth-Timely:

   Using git:
   ```bash
   cd path_to_opauth/Strategy
   git clone https://github.com/t1mmen/opauth-timely.git timely
   ```

  Or, using [Composer](https://getcomposer.org/), just add this to your `composer.json`:

   ```bash
   {
       "require": {
           "t1mmen/opauth-timely": "*"
       }
   }
   ```
   Then run `composer install`.


2. Create Timely application at https://timelyapp.com/:your_app_id/oauth_applications

3. Configure Opauth-Timely strategy with at least `Client ID` and `Client Secret`.

4. Direct user to `http://path_to_opauth/timely` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Timely' => array(
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET'
)
```

License
---------
Opauth-Timely is MIT Licensed
Copyright © 2015 Timm Stokke (http://timm.stokke.me)

[1]: https://github.com/opauth/opauth
