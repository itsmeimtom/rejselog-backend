# Rejselog Backend

This is the backend (saving/loading data) for the Rejselog app.

Disclaimer for the Davids of the world: I am very very well aware how janky this is. I am not at all bothered. It works, and that's all that matters.  
This is not intended to be a production-ready service, rather something an individual can run on their own server behind another layer of authentication, maybe Cloudflare Access?

Why PHP? Can just shove the files anywhere and it'll work. No need for babysitting pm2 or anything. Why SQLite? Why would this need anything more?

Repository for the frontend (all the main stuff!) is at [itsmeimtom/rejselog](https://github.com/itsmeimtom/rejselog).

## DB Structure
The DB is just SQLite with a single table. The table is called `saves` and has the following columns:
- `user` - Username of the user
- `pass` - User's password, hashed with Bcrypt
- `data` - Their data (journeys), Base64 encoded JSON string (can get quite big)

### Adding a user
Edit the DB with something like [this program](https://sqlitebrowser.org/) or CLI tools.

Passwords can be generated using the PHP function `password_hash()`. Run the PHP interactive shell with `php -a` and then run `echo(password_hash("hunter2", PASSWORD_BCRYPT));` to get the hash.  
In the future this may be improved, but for now, it'll do.