
rem 启动 redis
start "" "./Redis/redis/redis-server.exe" 

sleep(3)

rem 启动 Socket
cd "./Socket"
start "" "start.bat" 

