# React Chat

This is a simple multi-user chat program experiment using React.

## Install

Simply cline this repository.

    git clone git://github.com/tyler-king/react-chat.git

And run these two commands setup the vendor files:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar install

Now you can call from console:

```bash
php server.php {PORT}
```

## Usage

For client connections simply type:

```bash
php client.php {host}:{post}
```

You will then be connected to the chat server where you can chat to other connected clients.

## Commands

A few commands wrote into the chat are...

* **Rename:** /rename [new name]
* **Private Message:** /pm [user name] [your message]

## Todo

Some things i'll be doing.

* JSON for communication
* Refactor code
* Move autoloading into composer
