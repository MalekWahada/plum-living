Installation
------------


### requirements : 
- php-soap ext (`sudo apt-get install php7.4-soap`)

### Installation

#### Via Docker :
```bash
$ cp .env .env.local
$ make install
```

#### Command line :
```bash
$ cp .env .env.local
$ nano .env.local #set database config 
$ composer install --no-scripts
$ bin/console d:d:c # optionnaly creade database
$ bin/console d:s:u --dump-sql --force # create schema
$ bin/console doctrine:migrations:sync-metadata-storage # init migrations metadata storage (schema change log table)
$ bin/console doctrine:migrations:version --add --all # force migrations
$ bin/console asset:install
$ yarn install --force
$ yarn build
$ yarn encore dev # / yarn encore prod
$ composer fixtures # load fixtures
$ symfony server:start # start the server (or make a vhost)
$ open http://localhost:8000/
```

### Récupération de la BDD & medias.
Il est possible de récupérer les médias et BDD automatiquement depuis la prod : 
- il faut déclencher le job gitlab 'dump_prod'
- ensuite lancer en local `composer import_prod`


## JWT token install 
[see install doc](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#generate-the-ssh-keys)


Load Plum kitchen data [Required]
------------

```shell script
$ composer fixtures
```


# PayPal accounts

In order to create payment gateway and test payments on dev env, here are the Paypal accounts

Gmail & PayPal Business:
```
Login: plum.paypal.business@gmail.com
Password: PlumPaypal!
Phone number: 06 86 59 60 08
Birthdate: 01/01/1990
```

Gmail & PayPal Customer:
```
Login: plum.paypal.customer@gmail.com
Password: PlumPaypal!
Phone number: 06 86 59 60 08
Birthdate: 01/01/1990
```

## Plugin documentation

https://github.com/Sylius/PayPalPlugin

Because of the way it is configured, it can not be put into fixtures

### Warning

In order to configure the gateway, the app must be accessible from the internet (online or with ngrock)

## Testing cards :
https://developer.paypal.com/docs/payflow/payflow-pro/payflow-pro-testing/

# Stripe accounts

Please, note that the account must be in dev env for dev purposes

Stripe Business
```
Login: plum.paypal.business@gmail.com
Password: PlUmStr1pe!2o2i
```

## Plugin documentation

https://github.com/FLUX-SE/SyliusPayumStripePlugin

## Zendesk API

Sign in to your [plum-kitchen zendesk](https://plum-kitchen.zendesk.com/admin/) administrator account in and update these `.env.local` variables as follows:  
  
  - `ZENDESK_API_SUBDOMAIN` value is the domain name `plum-kitchen`
  - `ZENDESK_API_USERNAME` stands for your account mail address
  - `ZENDESK_API_TOKEN` is generated from [here](https://plum-kitchen.zendesk.com/agent/admin/api/settings)  
  - `ZENDESK_SUPPORT_CHAT_BOT_KEY` from the [script](https://plum-kitchen.zendesk.com/agent/admin/widget/setup_zopim) tag src extract the `key` query parameter value.  
```
ZENDESK_API_SUBDOMAIN=
ZENDESK_API_USERNAME=
ZENDESK_API_TOKEN=
ZENDESK_SUPPORT_CHAT_BOT_KEY=
```

## Mailing

Set the `MAILER_URL`, `MAIL_ADDRESS` and `MAIL_SENDER_NAME` values in your `.env.local`.
```
MAIL_SENDER_NAME=Plum
```
## Currency rounding mode

We are storing money value as tenth of cent, and no longer in cents. So please, make sure you insert the correct values in DB.

## Erp synchronisation

Todo : define a cron (to be defined)
`$ php bin/console app:erp:sync`

## MailChimp
To subscribe in the newsletter, add api key and list id from your account mailchimp

```
MAIL_CHIMP_API_KEY=YOUR_KEY
MAIL_CHIMP_LIST_ID=YOUR_LIST_ID
```

## Local Quality test

Run
`\$ composer test`

## Hotjar 

To track users' activities add your site id  
```
HOTJAR_SITE_ID=
```
