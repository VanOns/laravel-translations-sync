# DeepL

You can sync the translations with [DeepL](https://www.deepl.com).

## Setup

To use DeepL as a translation provider, follow these steps:

### Account

1. Create a DeepL account.
2. Create an API key from your [account](https://www.deepl.com/en/your-account/keys) and note it down.
3. Use the correct endpoint depending on your access level:
    - Free: `https://api-free.deepl.com`
    - Pro: `https://api.deepl.com`

### Configuration

Add the following configuration to your `.env` file:

```dotenv
LTS_TRANSLATE_PROVIDER=deepl
LTS_DEEPL_API_KEY=1111-2222-3333-4444-5555:aa
LTS_DEEPL_API_URL=https://api-free.deepl.com # or https://api.deepl.com
```

---

Congratulations! You have successfully set up DeepL as the translation provider.
