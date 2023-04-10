# Chat-with-Slim

How to Run the Chat App
This is a PHP application that uses the Slim micro-framework and SQLite database to create a simple messaging system. The app consists of four endpoints for creating users, getting all users, sending messages, and getting all messages for a given user.

To run the app, follow these steps:

Install PHP 7.4 or newer and SQLite3 on your machine, if you haven't already.

Install the required dependencies by running composer install in the project root directory. If you don't have Composer installed, you can download it from https://getcomposer.org/.

Create a new SQLite database by running touch chat.db in the project root directory.

Create the necessary database tables by running the following commands:

```sql
chat.db
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sender_id INTEGER NOT NULL,
    receiver_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(sender_id) REFERENCES users(id),
    FOREIGN KEY(receiver_id) REFERENCES users(id)
);

```
Start the PHP built-in web server by running php -S localhost:9000 in the project root directory.

Now you can test the API endpoints by sending HTTP requests to http://localhost:9000/. You can use a tool like Postman or cURL to send requests.

Here are some example requests:

**Create a new user**

POST /users
``` JSON
{
    "name": "John"
}
```
**Get all users**

GET /users


**Send a message**

POST /messages
``` JSON
{
    "sender_id": 1,
    "receiver_id": 2,
    "content": "Hello"
}
```
**Get all messages for a user**

GET /messages/2
