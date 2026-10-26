---
updatedAt: 2026-10-07T12:52:42.000Z
agentTools:
  projectIndex: https://docs.liteapi.travel/llms.txt
---

# Get tour details

## Overview

Retrieve full details for a specific tour, including description, media, inclusions, pricing context, and structured itineraries when available.

## When to Use

- **Product pages** - Display a tour detail view before the user selects dates
- **Comparison** - Show full metadata when comparing activities
- **Content enrichment** - Fetch descriptions and images for marketing surfaces

## What You Get

- **Complete tour profile** - Title, description, duration, and highlights
- **Media** - Images and cover assets
- **Practical info** - Meeting points, cancellation policy, and inclusions
- **Structured itineraries** - Ordered days/items (`itineraries`) when the provider supplies them. `locations` are importance-ordered tags, not itinerary order.
- **Localized pricing** - Prices in the requested currency

## Quick Start

Provide the tour `id` in the URL path plus required `language` and `currency` query parameters.

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
      },
      "tourId": {
        "name": "id",
        "in": "path",
        "required": true,
        "description": "Tour identifier",
        "schema": {
          "type": "string"
        },
        "example": "1218792"
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
      "TourItineraryItemDurationResponse": {
        "type": "object",
        "properties": {
          "value": {
            "type": "number",
            "nullable": true,
            "example": 45
          },
          "unit": {
            "type": "string",
            "nullable": true,
            "example": "minutes"
          }
        }
      },
      "TourItineraryItemLocationResponse": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string",
            "nullable": true,
            "example": "Eiffel Tower"
          },
          "coordinates": {
            "$ref": "#/components/schemas/TourCoordinatesResponse"
          }
        }
      },
      "TourItineraryItemResponse": {
        "type": "object",
        "required": [
          "type"
        ],
        "properties": {
          "type": {
            "type": "string",
            "description": "Itinerary stop/segment type, e.g. `stop`, `transfer`, `break`.",
            "example": "stop"
          },
          "title": {
            "type": "string",
            "nullable": true,
            "example": "Eiffel Tower"
          },
          "description": {
            "type": "string",
            "nullable": true,
            "example": "Skip-the-line entry"
          },
          "startTime": {
            "type": "string",
            "nullable": true,
            "example": "07:00"
          },
          "duration": {
            "$ref": "#/components/schemas/TourItineraryItemDurationResponse"
          },
          "location": {
            "$ref": "#/components/schemas/TourItineraryItemLocationResponse"
          },
          "optional": {
            "type": "boolean",
            "nullable": true
          },
          "extraCost": {
            "type": "boolean",
            "nullable": true
          },
          "entryIncluded": {
            "type": "boolean",
            "nullable": true
          },
          "mealType": {
            "type": "string",
            "nullable": true
          },
          "transportationType": {
            "type": "string",
            "nullable": true
          }
        }
      },
      "TourItineraryResponse": {
        "type": "object",
        "description": "One day (or the only day) of a structured itinerary. Empty `itineraries` on the tour means the provider has no structured itinerary data.",
        "properties": {
          "dayNumber": {
            "type": "integer",
            "nullable": true,
            "example": 1
          },
          "title": {
            "type": "string",
            "nullable": true,
            "example": "Morning tour"
          },
          "items": {
            "type": "array",
            "items": {
              "$ref": "#/components/schemas/TourItineraryItemResponse"
            }
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
      "TourDetailResponse": {
        "description": "Full tour detail including structured itineraries.",
        "allOf": [
          {
            "$ref": "#/components/schemas/TourBaseResponse"
          },
          {
            "type": "object",
            "properties": {
              "notSuitableFor": {
                "type": "array",
                "items": {
                  "type": "string"
                },
                "description": "Individual suitability restrictions from the provider.",
                "example": [
                  "Guests with mobility impairments",
                  "Pregnant travelers"
                ]
              },
              "itineraries": {
                "type": "array",
                "description": "Ordered structured itinerary. Empty when the provider has no structured itinerary data.",
                "items": {
                  "$ref": "#/components/schemas/TourItineraryResponse"
                }
              }
            }
          }
        ]
      },
      "TourDetailEnvelope": {
        "type": "object",
        "required": [
          "data"
        ],
        "properties": {
          "data": {
            "$ref": "#/components/schemas/TourDetailResponse"
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
      "NotFound": {
        "description": "Resource not found",
        "content": {
          "application/json": {
            "schema": {
              "$ref": "#/components/schemas/Error"
            },
            "examples": {
              "tourNotFound": {
                "summary": "Tour id does not exist",
                "value": {
                  "error": {
                    "code": 401,
                    "message": "invalid tour id",
                    "description": "tour not found"
                  }
                }
              },
              "bookingNotFound": {
                "summary": "Booking id does not exist, or doesn't belong to this account",
                "value": {
                  "error": {
                    "code": 4008,
                    "message": "bookingId not found",
                    "description": "Booking not found"
                  }
                }
              }
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
    "/experiences/tours/{id}": {
      "get": {
        "operationId": "getExperienceTour",
        "summary": "Get tour details",
        "description": "## Overview\n\nRetrieve full details for a specific tour, including description, media, inclusions, pricing context, and structured itineraries when available.\n\n## When to Use\n\n- **Product pages** - Display a tour detail view before the user selects dates\n- **Comparison** - Show full metadata when comparing activities\n- **Content enrichment** - Fetch descriptions and images for marketing surfaces\n\n## What You Get\n\n- **Complete tour profile** - Title, description, duration, and highlights\n- **Media** - Images and cover assets\n- **Practical info** - Meeting points, cancellation policy, and inclusions\n- **Structured itineraries** - Ordered days/items (`itineraries`) when the provider supplies them. `locations` are importance-ordered tags, not itinerary order.\n- **Localized pricing** - Prices in the requested currency\n\n## Quick Start\n\nProvide the tour `id` in the URL path plus required `language` and `currency` query parameters.",
        "tags": [
          "Tours"
        ],
        "parameters": [
          {
            "$ref": "#/components/parameters/tourId"
          },
          {
            "$ref": "#/components/parameters/language"
          },
          {
            "$ref": "#/components/parameters/currency"
          }
        ],
        "responses": {
          "200": {
            "description": "Tour detail",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/TourDetailEnvelope"
                },
                "example": {
                  "data": {
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
                    "notSuitableFor": [
                      "Guests with mobility impairments",
                      "Pregnant travelers"
                    ],
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
                    },
                    "itineraries": [
                      {
                        "dayNumber": 1,
                        "title": "Morning tour",
                        "items": [
                          {
                            "type": "stop",
                            "title": "Eiffel Tower",
                            "description": "Skip-the-line entry",
                            "startTime": "07:00",
                            "duration": {
                              "value": 45,
                              "unit": "minutes"
                            },
                            "location": {
                              "name": "Eiffel Tower",
                              "coordinates": {
                                "lat": 48.8584,
                                "long": 2.2945
                              }
                            },
                            "optional": false,
                            "extraCost": false,
                            "entryIncluded": true,
                            "mealType": null,
                            "transportationType": null
                          }
                        ]
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
          "404": {
            "$ref": "#/components/responses/NotFound"
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