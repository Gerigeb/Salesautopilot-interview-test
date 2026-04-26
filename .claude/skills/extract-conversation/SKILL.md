---
name: extract-conversations
description: "Extracts the agent conversation with direct user prompts"
---

# Principles
- Always include user prompts from conversation as is without any modification.
- Instead of including file changes just reference the changed file name.
- Summarize the agent responses.

# Examples
```md
### Daily weather
Kiss Gergely: What is the weather like today?
Claude AI: It's a sunny day with little wind, it is 23 degrees outside.
```

```md
### Env file cleanup
Kiss Gergely: Remove the unnecessary variables from the env and env example file
Claude AI: .env / .env.example — removed unnecessary variables
```

```md
### Remove caching
Kiss Gergely: remove caching from the code base (include unused env, env examples variables as well)
Claude: The changes are complete:
- ApiClient.php — removed Cache import, $cacheTtl param, and Cache::remember() wrapper; get() now calls $this->request() directly
- ApiClientTest.php — removed Cache import, dropped the cache-hit test, simplified the remaining test
- .env / .env.example — removed CACHE_STORE=file   
```

# User prompts
Always export the full user messages as is.
Format for the message: "Kiss Gergely: {user_message}"

# Responses
Summarize the response to the user messages.
Format for the message: "Claude AI: {agent_response}"

# Export
Handle all export in a single file called AI_LOG.md in the docs folder, append the existing file with newer conversations at the end of the file,
separate different conversations with a 2-3 word summary heeder title
