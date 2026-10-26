---
updatedAt: 2026-06-02T09:22:54.000Z
agentTools:
  projectIndex: https://docs.liteapi.travel/llms.txt
---

# List of hotel types

## Overview

Get all available hotel type classifications (e.g., resort, boutique, business hotel). Use type IDs to filter hotel searches.

## When to Use

- **Type filters** - Filter hotels by type in search
- **Type display** - Show hotel type classifications
- **Reference data** - Get hotel type IDs for filtering

## What You Get

- **Hotel type list** - All available hotel types
- **Type IDs** - Numeric IDs for use in search filters
- **Type names** - Hotel type classifications

## Quick Start

No parameters required. Returns all hotel types with IDs. Use type IDs in hotel search filters.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "API: Hotel data",
    "version": "3.0.0"
  },
  "servers": [
    {
      "url": "https://api.liteapi.travel/v3.0"
    }
  ],
  "security": [
    {
      "apikeyAuth": []
    }
  ],
  "paths": {
    "/data/hotelTypes": {
      "get": {
        "tags": [
          "Hotel Types"
        ],
        "summary": "List of hotel types",
        "description": "## Overview\n\nGet all available hotel type classifications (e.g., resort, boutique, business hotel). Use type IDs to filter hotel searches.\n\n## When to Use\n\n- **Type filters** - Filter hotels by type in search\n- **Type display** - Show hotel type classifications\n- **Reference data** - Get hotel type IDs for filtering\n\n## What You Get\n\n- **Hotel type list** - All available hotel types\n- **Type IDs** - Numeric IDs for use in search filters\n- **Type names** - Hotel type classifications\n\n## Quick Start\n\nNo parameters required. Returns all hotel types with IDs. Use type IDs in hotel search filters.",
        "security": [
          {
            "apikeyAuth": []
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "headers": {
              "Content-Type": {
                "schema": {
                  "type": "string",
                  "example": "application/json; charset=utf-8"
                }
              },
              "Content-Encoding": {
                "schema": {
                  "type": "string",
                  "example": "gzip"
                }
              }
            },
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "data": {
                      "type": "array",
                      "items": {
                        "type": "object",
                        "required": [
                          "id",
                          "name"
                        ],
                        "properties": {
                          "id": {
                            "type": "number",
                            "description": "A numeric identifier for the hotel or property type."
                          },
                          "name": {
                            "type": "string",
                            "description": "The name or category of the hotel or property type."
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "apikeyAuth": {
        "name": "X-API-Key",
        "type": "apiKey",
        "in": "header"
      }
    }
  },
  "x-readme": {
    "explorer-enabled": true,
    "proxy-enabled": true,
    "samples-enabled": true
  }
}
```