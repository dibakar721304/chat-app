
<!--Write a chat application backend in PHP. Users should be able to create chat groups, join these groups and send messages within them. For simplicity, the groups are public: any user can join any group. Users should be able to list all the messages within a group.-->

# Tech stack:
a)SQLite version 3.42.0 
b)Slim 4 ( please check composer.json)
c)Postman v10.14( for testing)
# Below are some command used:
a) To start server:
php -S localhost:9998 -t public
b)To run tests:
./vendor/bin/phpunit <filePath>
c)To run formatter:
vendor/bin/php-cs-fixer fix  

# Below are the usage of end points from postmman:

1) Users end points

    a) create user:
       POST http://localhost:9998/users
       body:
       {
            "username" : "testName"
       }
    b) Fetch user details :
        GET http://localhost:9998/users

2) Chat group end points:
    a) Create chat group:
        POST http://localhost:9998/groups
        body:
        {
            "name":"testChatGroup"
        }
    b) Fetch chat groups:
        GET http://localhost:9998/groups
    c) Join a chat group:
        POST http://localhost:9998/groups/join/
        body:
        {
            "groupId":1,
            "userId" :1
        }
    d) Leaving a chat group:
        POST http://localhost:9998/groups/leave/
        body:
        {
            "groupId":1,
            "userId" :1
        }
3) Chat messages end points:
    a) Send message:
        POST http://localhost:9998/chats/message/
        body:
        {
            "message":"testMessage",
            "userId" :1,
            "groupId":1
        }
    b) Retriving latest messages:
        GET http://localhost:9998/messages/chats/{groupId}/{userId}?groupId=1&userId=1

