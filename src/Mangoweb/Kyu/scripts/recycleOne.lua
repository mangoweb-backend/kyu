-- A Redis script is transactional by definition
-- http://redis.io/topics/transactions

local channel = KEYS[1]

-- get oldest message in processing list
local messageId = redis.call('RPOP', channel .. ".processing")

if messageId == nil then
    -- processing list is empty
    return nil
end

if true == redis.call('EXISTS', channel .. ".alive." .. messageId) then
    -- oldest item in processing list is not timed-out yet
    return nil
end

local rawJson = redis.call('GET', channel .. ".value." .. messageId)
local message = cjson.decode(rawJson)

-- last processing attempt cost us one counter, decrement
message['counter'] = message['counter'] - 1

-- if at least one processing attempt remains, reinsert to queue
if message['counter'] >= 1 then
    -- item has remaining retries, update counter and reinsert to queue
    redis.call('SET', channel .. ".value." .. messageId, cjson.encode(message))
    redis.call('LPUSH', channel .. ".queue", messageId)

    -- set value used by Message::isFailedPermanently()
    message['failed'] = false
    return cjson.encode(message)
else
    message['failed'] = true
    return cjson.encode(message)
end
