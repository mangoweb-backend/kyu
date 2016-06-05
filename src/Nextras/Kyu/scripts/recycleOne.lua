redis.call('WATCH', '')
redis.call('MULTI')
-- cjson.decode
-- ...
redis.call('EXEC')
