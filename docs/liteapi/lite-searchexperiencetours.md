---
updatedAt: 2026-10-07T12:52:42.000Z
agentTools:
  projectIndex: https://docs.liteapi.travel/llms.txt
---

# Search tours

## Overview

Search available tours and activities with localized content and prices in your chosen currency. Supports free-text or geo search, plus filtering by date, language, price, category, rating, duration, and tour flags.

## When to Use

- **Search results** - Populate a tours listing or map view
- **Destination pages** - Show activities available in a city or region
- **Category browsing** - Filter tours by type, duration, or rating

## What You Get

- **Tour listings** - Titles, descriptions, images, and ratings
- **Localized content** - Names and descriptions in the requested language
- **Prices** - Amounts in the requested currency

## Quick Start

**Required:** `language`, `currency`

**Search (pick one):** `textSearch`, or `location[latitude]`/`location[longitude]`/`location[radiusKm]`

**Filters:** `dateRange[from]`/`dateRange[to]`, `languages`, `price[min]`/`price[max]`, `categories`, `rating[min]`/`rating[max]`, `duration[min]`/`duration[max]`, `flags`

**Sort:** `sort[field]`/`sort[direction]`

**Pagination:** `pagination[limit]`/`pagination[offset]`

Returns a paginated list of matching tours.

# OpenAPI definition

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "API: Experiences",
    "version": "3.0.0",
    "description": "The Experiences API lets you search tours and activities, check availability, reviews, and booking options. Access must be enabled on your account."
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
  "tags": [
    {
      "name": "Tours",
      "description": "Search tours, check availability and reviews, and price booking options for tours and activities."
    }
  ],
  "components": {
    "securitySchemes": {
      "apikeyAuth": {
        "name": "X-API-Key",
        "type": "apiKey",
        "in": "header"
      }
    },
    "parameters": {
      "language": {
        "name": "language",
        "in": "query",
        "required": true,
        "description": "ISO language code for localized content",
        "schema": {
          "type": "string"
        },
        "example": "en"
      },
      "currency": {
        "name": "currency",
        "in": "query",
        "required": true,
        "description": "ISO 4217 currency code for prices",
        "schema": {
          "type": "string"
        },
        "example": "EUR"
      }
    },
    "schemas": {
      "CanonicalBookingValidationError": {
        "type": "object",
        "properties": {
          "type": {
            "type": "string",
            "enum": [
              "validation_error"
            ]
          },
          "details": {
            "type": "array",
            "items": {
              "type": "object",
              "properties": {
                "fieldId": {
                  "type": "string"
                },
                "participantIndex": {
                  "type": "integer",
                  "nullable": true
                },
                "code": {
                  "type": "string"
                },
                "reason": {
                  "type": "string"
                }
              }
            }
          }
        }
      },
      "Error": {
        "type": "object",
        "required": [
          "error"
        ],
        "properties": {
          "error": {
            "type": "object",
            "properties": {
              "code": {
                "type": "integer",
                "description": "Machine-readable error code"
              },
              "message": {
                "type": "string",
                "description": "Human-readable error message"
              },
              "description": {
                "description": "Additional error context. Prebook requests can relay canonical booking validation details.",
                "oneOf": [
                  {
                    "type": "string"
                  },
                  {
                    "$ref": "#/components/schemas/CanonicalBookingValidationError"
                  }
                ]
              },
              "key": {
                "type": "string",
                "description": "Dot-path to the offending field in the request body, present on field-level validation errors (e.g. `bodyRequest.selection.price.amount`)."
              }
            }
          }
        }
      },
      "TourCoordinatesResponse": {
        "type": "object",
        "properties": {
          "lat": {
            "type": "number",
            "format": "double",
            "example": 48.8584
          },
          "long": {
            "type": "number",
            "format": "double",
            "example": 2.2945
          }
        }
      },
      "TourPictureResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "integer",
            "example": 1
          },
          "url": {
            "type": "string",
            "format": "uri",
            "example": "https://cdn.example.com/tour-903304-1.jpg"
          },
          "verified": {
            "type": "boolean",
            "example": true
          },
          "copyright": {
            "type": "string",
            "nullable": true
          }
        }
      },
      "TourDealSummaryResponse": {
        "type": "object",
        "description": "Present only when a promotional deal applies to the tour's from-price.",
        "properties": {
          "originalPrice": {
            "type": "number",
            "example": 56.9
          },
          "currency": {
            "type": "string",
            "example": "EUR"
          },
          "percentage": {
            "type": "number",
            "description": "Discount percentage, e.g. 20 for 20% off.",
            "example": 20
          },
          "dateRange": {
            "type": "object",
            "properties": {
              "start": {
                "type": "string",
                "format": "date",
                "nullable": true
              },
              "end": {
                "type": "string",
                "format": "date",
                "nullable": true
              }
            }
          }
        }
      },
      "TourPriceResponse": {
        "type": "object",
        "properties": {
          "amount": {
            "type": "number",
            "example": 45.5
          },
          "currency": {
            "type": "string",
            "example": "EUR"
          },
          "dealSummary": {
            "$ref": "#/components/schemas/TourDealSummaryResponse"
          }
        }
      },
      "TourCategoryResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "integer",
            "example": 12
          },
          "name": {
            "type": "string",
            "example": "Sightseeing"
          }
        }
      },
      "TourLocationResponse": {
        "type": "object",
        "properties": {
          "id": {
            "type": "integer",
            "example": 1
          },
          "type": {
            "type": "string",
            "example": "city"
          },
          "name": {
            "type": "string",
            "example": "Paris"
          },
          "city": {
            "type": "string",
            "example": "Paris"
          },
          "country": {
            "type": "string",
            "example": "FR"
          },
          "coordinates": {
            "$ref": "#/components/schemas/TourCoordinatesResponse"
          },
          "parentId": {
            "type": "integer",
            "example": 0
          }
        }
      },
      "TourDurationResponse": {
        "type": "object",
        "properties": {
          "value": {
            "type": "number",
            "example": 2
          },
          "unit": {
            "type": "string",
            "example": "hours"
          }
        }
      },
      "TourOpeningHoursResponse": {
        "type": "object",
        "properties": {
          "from": {
            "type": "string",
            "example": "09:00"
          },
          "to": {
            "type": "string",
            "example": "18:00"
          }
        }
      },
      "TourCancellationPolicyResponse": {
        "type": "object",
        "properties": {
          "cancellable": {
            "type": "boolean",
            "example": true
          },
          "description": {
            "type": "string",
            "example": "Free cancellation up to 24 hours before the experience starts"
          },
          "cancellationFeeUnitFeeUnit": {
            "type": "string",
            "example": "percent"
          },
          "cancellationFeeUnitFeeValue": {
            "type": "number",
            "example": 100
          },
          "cancellationFeeUnitOffsetMin": {
            "type": "integer",
            "description": "Minutes before start time the fee schedule above applies.",
            "example": 1440
          },
          "refundable": {
            "type": "boolean",
            "example": true
          },
          "partlyRefundable": {
            "type": "boolean",
            "example": false
          }
        }
      },
      "TourBaseResponse": {
        "type": "object",
        "description": "Shared tour fields returned by both search listings and the detail endpoint.",
        "properties": {
          "id": {
            "type": "integer",
            "example": 903304
          },
          "title": {
            "type": "string",
            "example": "Skip-the-line morning tour"
          },
          "description": {
            "type": "string",
            "example": "A guided morning tour with skip-the-line access."
          },
          "placeDetails": {
            "type": "string",
            "example": "Meet at the main entrance."
          },
          "additionalInfo": {
            "type": "string",
            "example": "Not wheelchair accessible."
          },
          "itemsToBring": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "example": [
              "Comfortable shoes"
            ]
          },
          "notAllowedItems": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "example": [
              "Large backpacks"
            ]
          },
          "badges": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "example": [
              "likely_to_sell_out"
            ]
          },
          "hasPickUp": {
            "type": "boolean",
            "example": true
          },
          "overallRating": {
            "type": "number",
            "example": 4.7
          },
          "numberOfRatings": {
            "type": "integer",
            "example": 1523
          },
          "highlights": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "example": [
              "Skip the line",
              "Expert local guide"
            ]
          },
          "included": {
            "type": "string",
            "example": "Entry ticket, guided tour"
          },
          "excluded": {
            "type": "string",
            "example": "Hotel pickup, gratuities"
          },
          "pictures": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/TourPictureResponse"
            }
          },
          "coordinates": {
            "$ref": "#/components/schemas/TourCoordinatesResponse"
          },
          "price": {
            "$ref": "#/components/schemas/TourPriceResponse"
          },
          "categories": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/TourCategoryResponse"
            }
          },
          "locations": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/TourLocationResponse"
            }
          },
          "availableLanguages": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "example": [
              "en",
              "fr"
            ]
          },
          "translationLanguages": {
            "type": "array",
            "items": {
              "type": "string"
            },
            "example": [
              "en",
              "fr",
              "es"
            ]
          },
          "guideType": {
            "type": "string",
            "enum": [
              "none",
              "live_guide",
              "audio_guide",
              "driver",
              "host_or_greeter",
              "instructor"
            ],
            "example": "live_guide"
          },
          "mobileVoucher": {
            "type": "boolean",
            "example": true
          },
          "freeSale": {
            "type": "boolean",
            "example": false
          },
          "activityType": {
            "type": "string",
            "example": "tour"
          },
          "partnerBusinessCategory": {
            "type": "string",
            "example": "attraction"
          },
          "priceDescription": {
            "type": "string",
            "example": "per person"
          },
          "sourceUrl": {
            "type": "string",
            "format": "uri",
            "example": "https://provider.example.com/tour/903304"
          },
          "durations": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/TourDurationResponse"
            }
          },
          "openingHours": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/TourOpeningHoursResponse"
            }
          },
          "cancellationPolicy": {
            "$ref": "#/components/schemas/TourCancellationPolicyResponse"
          }
        }
      },
      "TourSummaryResponse": {
        "description": "Tour listing as returned by tour search. The list mapper collapses `notSuitableFor` to a single string. Use `GET /experiences/tours/{id}` for the full detail payload including `itineraries`.",
        "allOf": [
          {
            "$ref": "#/components/schemas/TourBaseResponse"
          },
          {
            "type": "object",
            "properties": {
              "notSuitableFor": {
                "type": "string",
                "description": "First suitability restriction from the provider, collapsed to a single string by the list mapper.",
                "example": "Guests with mobility impairments"
              }
            }
          }
        ]
      },
      "ToursMetaResponse": {
        "type": "object",
        "properties": {
          "totalCount": {
            "type": "integer",
            "example": 102845
          },
          "limit": {
            "type": "integer",
            "example": 20
          },
          "offset": {
            "type": "integer",
            "example": 0
          }
        }
      },
      "ToursListResponse": {
        "type": "object",
        "properties": {
          "meta": {
            "$ref": "#/components/schemas/ToursMetaResponse"
          },
          "tours": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/TourSummaryResponse"
            }
          }
        }
      },
      "ToursListEnvelope": {
        "type": "object",
        "required": [
          "data"
        ],
        "properties": {
          "data": {
            "$ref": "#/components/schemas/ToursListResponse"
          }
        }
      }
    },
    "responses": {
      "BadRequest": {
        "description": "Validation error",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/Error"
            },
            "examples": {
              "relayedValidationError": {
                "summary": "Relayed from the provider (e.g. malformed booking-options request)",
                "value": {
                  "error": {
                    "code": 400,
                    "message": "bad request",
                    "description": "participants must be an object containing participant counts by age band, e.g. {\"adult\": 2}"
                  }
                }
              },
              "missingRequiredField": {
                "summary": "Required-field validation",
                "value": {
                  "error": {
                    "code": 4002,
                    "message": "required request field is missing or wrong input",
                    "description": "field 'Currency' is required",
                    "key": "bodyRequest.currency"
                  }
                }
              }
            }
          }
        }
      },
      "Unauthorized": {
        "description": "Authentication required",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/Error"
            }
          }
        }
      },
      "Forbidden": {
        "description": "Experiences access is not enabled for this account",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/Error"
            }
          }
        }
      },
      "BadGateway": {
        "description": "Failed to reach the experiences provider",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/Error"
            }
          }
        }
      }
    }
  },
  "paths": {
    "/experiences/tours": {
      "get": {
        "operationId": "searchExperienceTours",
        "summary": "Search tours",
        "description": "## Overview\n\nSearch available tours and activities with localized content and prices in your chosen currency. Supports free-text or geo search, plus filtering by date, language, price, category, rating, duration, and tour flags.\n\n## When to Use\n\n- **Search results** - Populate a tours listing or map view\n- **Destination pages** - Show activities available in a city or region\n- **Category browsing** - Filter tours by type, duration, or rating\n\n## What You Get\n\n- **Tour listings** - Titles, descriptions, images, and ratings\n- **Localized content** - Names and descriptions in the requested language\n- **Prices** - Amounts in the requested currency\n\n## Quick Start\n\n**Required:** `language`, `currency`\n\n**Search (pick one):** `textSearch`, or `location[latitude]`/`location[longitude]`/`location[radiusKm]`\n\n**Filters:** `dateRange[from]`/`dateRange[to]`, `languages`, `price[min]`/`price[max]`, `categories`, `rating[min]`/`rating[max]`, `duration[min]`/`duration[max]`, `flags`\n\n**Sort:** `sort[field]`/`sort[direction]`\n\n**Pagination:** `pagination[limit]`/`pagination[offset]`\n\nReturns a paginated list of matching tours.",
        "tags": [
          "Tours"
        ],
        "parameters": [
          {
            "$ref": "#/components/parameters/language"
          },
          {
            "$ref": "#/components/parameters/currency"
          },
          {
            "name": "textSearch",
            "in": "query",
            "required": false,
            "description": "Free-text search term (location name, tour title, etc). Mutually exclusive with `location[latitude]`/`location[longitude]`.",
            "schema": {
              "type": "string"
            }
          },
          {
            "name": "location[latitude]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Geo filter latitude. Must be sent together with `location[longitude]` and `location[radiusKm]`. Mutually exclusive with `textSearch`.",
            "schema": {
              "type": "number"
            }
          },
          {
            "name": "location[longitude]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Geo filter longitude. Must be sent together with `location[latitude]` and `location[radiusKm]`.",
            "schema": {
              "type": "number"
            }
          },
          {
            "name": "location[radiusKm]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Geo filter radius in kilometers around `location[latitude]`/`location[longitude]`.",
            "schema": {
              "type": "number"
            }
          },
          {
            "name": "dateRange[from]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Only return tours offered on or after this date (YYYY-MM-DD).",
            "schema": {
              "type": "string",
              "format": "date"
            }
          },
          {
            "name": "dateRange[to]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Only return tours offered on or before this date (YYYY-MM-DD).",
            "schema": {
              "type": "string",
              "format": "date"
            }
          },
          {
            "name": "languages",
            "in": "query",
            "required": false,
            "style": "form",
            "explode": true,
            "description": "Filter to tours conducted in one or more of these ISO language codes (repeat the param per value, e.g. `languages=en&languages=fr`). Distinct from the required `language` param, which only controls localized content on the response.",
            "schema": {
              "type": "array",
              "items": {
                "type": "string"
              }
            }
          },
          {
            "name": "price[min]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Minimum partner-facing selling price, in the requested `currency`. Bounds are interpreted using the same cent rounding as the returned price.",
            "schema": {
              "type": "number"
            }
          },
          {
            "name": "price[max]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Maximum partner-facing selling price, in the requested `currency`. Bounds are interpreted using the same cent rounding as the returned price.",
            "schema": {
              "type": "number"
            }
          },
          {
            "name": "categories",
            "in": "query",
            "required": false,
            "style": "form",
            "explode": true,
            "description": "Filter to one or more category ids (repeat the param per value, e.g. `categories=1&categories=2`). See the category `id`/`name` pairs returned on each tour.",
            "schema": {
              "type": "array",
              "items": {
                "type": "integer"
              }
            }
          },
          {
            "name": "rating[min]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Minimum overall tour rating (0-5).",
            "schema": {
              "type": "number"
            }
          },
          {
            "name": "rating[max]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Maximum overall tour rating (0-5).",
            "schema": {
              "type": "number"
            }
          },
          {
            "name": "duration[min]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Minimum tour duration in minutes.",
            "schema": {
              "type": "integer"
            }
          },
          {
            "name": "duration[max]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Maximum tour duration in minutes.",
            "schema": {
              "type": "integer"
            }
          },
          {
            "name": "flags",
            "in": "query",
            "required": false,
            "style": "form",
            "explode": true,
            "description": "Filter to tours matching one or more of these properties (repeat the param per value, e.g. `flags=wheelchair&flags=pick-up`).",
            "schema": {
              "type": "array",
              "items": {
                "type": "string",
                "enum": [
                  "private",
                  "wheelchair",
                  "skip-line",
                  "pick-up",
                  "special",
                  "free-sale-only",
                  "mobile-voucher-only"
                ]
              }
            }
          },
          {
            "name": "sort[field]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Field to sort results by. `popularity` returns an internal recommended ranking and ignores `sort[direction]`.",
            "schema": {
              "type": "string",
              "enum": [
                "popularity",
                "price",
                "rating",
                "duration"
              ]
            }
          },
          {
            "name": "sort[direction]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Sort direction. Ignored when `sort[field]=popularity`.",
            "schema": {
              "type": "string",
              "enum": [
                "ASC",
                "DESC"
              ]
            }
          },
          {
            "name": "pagination[limit]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Maximum number of tours to return.",
            "schema": {
              "type": "integer"
            }
          },
          {
            "name": "pagination[offset]",
            "in": "query",
            "required": false,
            "allowReserved": true,
            "description": "Number of tours to skip, for pagination.",
            "schema": {
              "type": "integer"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "List of tours",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/ToursListEnvelope"
                },
                "example": {
                  "data": {
                    "meta": {
                      "totalCount": 102845,
                      "limit": 20,
                      "offset": 0
                    },
                    "tours": [
                      {
                        "id": 903304,
                        "title": "Skip-the-line morning tour",
                        "description": "A guided morning tour with skip-the-line access.",
                        "placeDetails": "Meet at the main entrance.",
                        "additionalInfo": "Not wheelchair accessible.",
                        "itemsToBring": [
                          "Comfortable shoes"
                        ],
                        "notAllowedItems": [
                          "Large backpacks"
                        ],
                        "notSuitableFor": "Guests with mobility impairments",
                        "badges": [
                          "likely_to_sell_out"
                        ],
                        "hasPickUp": true,
                        "overallRating": 4.7,
                        "numberOfRatings": 1523,
                        "highlights": [
                          "Skip the line",
                          "Expert local guide"
                        ],
                        "included": "Entry ticket, guided tour",
                        "excluded": "Hotel pickup, gratuities",
                        "pictures": [
                          {
                            "id": 1,
                            "url": "https://cdn.example.com/tour-903304-1.jpg",
                            "verified": true
                          }
                        ],
                        "coordinates": {
                          "lat": 48.8584,
                          "long": 2.2945
                        },
                        "price": {
                          "amount": 45.5,
                          "currency": "EUR"
                        },
                        "categories": [
                          {
                            "id": 12,
                            "name": "Sightseeing"
                          }
                        ],
                        "locations": [
                          {
                            "id": 1,
                            "type": "city",
                            "name": "Paris",
                            "city": "Paris",
                            "country": "FR",
                            "coordinates": {
                              "lat": 48.8566,
                              "long": 2.3522
                            },
                            "parentId": 0
                          }
                        ],
                        "availableLanguages": [
                          "en",
                          "fr"
                        ],
                        "translationLanguages": [
                          "en",
                          "fr",
                          "es"
                        ],
                        "guideType": "live_guide",
                        "mobileVoucher": true,
                        "freeSale": false,
                        "activityType": "tour",
                        "partnerBusinessCategory": "attraction",
                        "priceDescription": "per person",
                        "sourceUrl": "https://provider.example.com/tour/903304",
                        "durations": [
                          {
                            "value": 2,
                            "unit": "hours"
                          }
                        ],
                        "openingHours": [
                          {
                            "from": "09:00",
                            "to": "18:00"
                          }
                        ],
                        "cancellationPolicy": {
                          "cancellable": true,
                          "description": "Free cancellation up to 24 hours before the experience starts",
                          "cancellationFeeUnitFeeUnit": "percent",
                          "cancellationFeeUnitFeeValue": 100,
                          "cancellationFeeUnitOffsetMin": 1440,
                          "refundable": true,
                          "partlyRefundable": false
                        }
                      }
                    ]
                  }
                }
              }
            }
          },
          "400": {
            "$ref": "#/components/responses/BadRequest"
          },
          "401": {
            "$ref": "#/components/responses/Unauthorized"
          },
          "403": {
            "$ref": "#/components/responses/Forbidden"
          },
          "502": {
            "$ref": "#/components/responses/BadGateway"
          }
        }
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