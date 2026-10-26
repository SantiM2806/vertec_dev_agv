---
updatedAt: 2026-06-02T09:22:54.000Z
agentTools:
  projectIndex: https://docs.liteapi.travel/llms.txt
---

# Search for a list of places

## Overview

Search for places, locations, and areas using Google Places API. Returns a list of matching places that can be used to search for hotels within specific boundaries.

**Pricing**: $0.01 per request

## When to Use

- **Location autocomplete** - Build location search with autocomplete suggestions
- **Place selection** - Let users select cities, airports, or areas
- **Hotel search boundaries** - Get Place IDs to restrict hotel searches to specific regions
- **Location discovery** - Find places by name or description

## What You Get

- **Place list** - Multiple matching places with details
- **Place IDs** - Unique identifiers for use in hotel searches
- **Location information** - Names, addresses, and location types
- **Formatted addresses** - Human-readable addresses for display

## Key Features

- **Multiple types** - Search for cities, airports, hotels, or other place types
- **Type filtering** - Specify place types (e.g., 'locality,airport,hotel')
- **Smart defaults** - Automatically excludes less relevant types unless specified
- **Relevance ordering** - Results sorted by relevance using Google's ranking

## Quick Start

Provide a `textQuery` (e.g., "Manhattan") and optionally specify `type` to filter results. Returns matching places with Place IDs you can use in hotel searches.

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
    "/data/places": {
      "get": {
        "tags": [
          "Places"
        ],
        "summary": "Search for a list of places",
        "description": "## Overview\n\nSearch for places, locations, and areas using Google Places API. Returns a list of matching places that can be used to search for hotels within specific boundaries.\n\n**Pricing**: $0.01 per request\n\n## When to Use\n\n- **Location autocomplete** - Build location search with autocomplete suggestions\n- **Place selection** - Let users select cities, airports, or areas\n- **Hotel search boundaries** - Get Place IDs to restrict hotel searches to specific regions\n- **Location discovery** - Find places by name or description\n\n## What You Get\n\n- **Place list** - Multiple matching places with details\n- **Place IDs** - Unique identifiers for use in hotel searches\n- **Location information** - Names, addresses, and location types\n- **Formatted addresses** - Human-readable addresses for display\n\n## Key Features\n\n- **Multiple types** - Search for cities, airports, hotels, or other place types\n- **Type filtering** - Specify place types (e.g., 'locality,airport,hotel')\n- **Smart defaults** - Automatically excludes less relevant types unless specified\n- **Relevance ordering** - Results sorted by relevance using Google's ranking\n\n## Quick Start\n\nProvide a `textQuery` (e.g., \"Manhattan\") and optionally specify `type` to filter results. Returns matching places with Place IDs you can use in hotel searches.",
        "security": [
          {
            "apikeyAuth": []
          }
        ],
        "parameters": [
          {
            "name": "textQuery",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Search query. e.g. 'Manhattan'",
            "required": true,
            "example": "Manhattan"
          },
          {
            "name": "type",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Restricts the results to places matching the specified type(s). You can specify a single type (e.g., 'hotel') or multiple types as a comma-separated list (e.g., 'locality,airport,hotel'). Common types include: 'locality' (cities), 'airport', 'hotel', 'lodging', 'establishment', 'point_of_interest'. When multiple types are provided, results from all types are merged and ordered by relevance.",
            "example": "hotel"
          },
          {
            "name": "language",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "The language code, indicating in which language the results should be returned. e.g. 'en'",
            "example": "en"
          },
          {
            "name": "clientIP",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "The IP address of the client making the request.",
            "example": "176.31.84.249"
          },
          {
            "name": "sessionId",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Optional Google Places billing session ID. When provided, this autocomplete call is bundled into a session settled by a subsequent place details call, reducing billing costs. Can also be passed via the X-Places-Session-Id header (header takes priority). If omitted, the server manages a fallback session automatically.",
            "example": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
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
                          "placeId",
                          "displayName",
                          "formattedAddress"
                        ],
                        "properties": {
                          "placeId": {
                            "type": "string",
                            "description": "A unique identifier for the place, typically used in further requests or lookups."
                          },
                          "displayName": {
                            "type": "string",
                            "description": "A human-friendly name or label for the place."
                          },
                          "formattedAddress": {
                            "type": "string",
                            "description": "The full address for the place, including street, city, country, etc."
                          },
                          "types": {
                            "type": "array",
                            "description": "A list of categories or designations describing the place (e.g. 'hotel', 'lodging').",
                            "items": {
                              "type": "string"
                            }
                          }
                        }
                      }
                    }
                  }
                },
                "example": {
                  "data": [
                    {
                      "placeId": "ChIJu1K2erNv5kcR6HyzBQieKJ0",
                      "displayName": "Rome",
                      "formattedAddress": "75017 París, Francia",
                      "types": [
                        "subway_station",
                        "transit_station",
                        "point_of_interest",
                        "establishment"
                      ]
                    },
                    {
                      "placeId": "ChIJNcQlLHTIzhIRSLrqN9rgv5Q",
                      "displayName": "Paris Rome",
                      "formattedAddress": "4 Rue de Provence, 75009 Paris, Francia",
                      "types": [
                        "hotel",
                        "lodging",
                        "point_of_interest",
                        "establishment"
                      ]
                    }
                  ]
                }
              }
            }
          },
          "400": {
            "description": "Bad request",
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
                    "error": {
                      "type": "object",
                      "properties": {
                        "code": {
                          "type": "integer",
                          "description": "Numeric error code representing the type of issue"
                        },
                        "description": {
                          "type": "string",
                          "description": "Brief explanation of the error type"
                        },
                        "message": {
                          "type": "string",
                          "description": "Message providing context about the error"
                        }
                      }
                    }
                  }
                },
                "example": {
                  "value": {
                    "error": {
                      "code": 4002,
                      "description": "query parameter 'textQuery' is required",
                      "message": "required request field is missing or wrong input"
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