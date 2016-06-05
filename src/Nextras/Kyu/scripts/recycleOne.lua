-- A Redis script is transactional by definition
-- http://redis.io/topics/transactions

local channel = KEYS[1]
local oldestMessageId = redis.call('RPOP', channel .. ".processing")

if oldestMessageId == nil then
    -- processing list is empty
    return nil
end

if true == redis.call('EXISTS', channel .. ".alive." .. oldestMessageId) then
    -- oldest item in processing list is not timed-out yet
    return nil
end

local rawJson = redis.call('GET', channel .. ".value." .. oldestMessageId)
local message = cjson.decode(rawJson)

if message['counter'] > 1 then
    -- item has remaining retries, update counter and reinsert to queue
    message['counter'] = message['counter'] - 1
    redis.call('LPUSH', channel .. ".queue", cjson.encode(message))

    -- set value used by Message::isFailedPermanently()
    message['failed'] = false
    return cjson.encode(message)
else
    message['failed'] = true
    return cjson.encode(message)
end
