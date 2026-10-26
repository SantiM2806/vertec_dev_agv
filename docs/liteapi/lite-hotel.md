---
updatedAt: 2026-06-02T09:22:54.000Z
agentTools:
  projectIndex: https://docs.liteapi.travel/llms.txt
---

# Get the details of a hotel

## Overview

Get comprehensive details about a specific hotel including descriptions, amenities, images, location, and ratings. Perfect for displaying hotel detail pages.

## When to Use

- **Hotel detail pages** - Show complete hotel information
- **Booking pages** - Display hotel details before booking
- **Hotel profiles** - Build rich hotel information pages
- **Content display** - Show descriptions, amenities, and images

## What You Get

- **Complete hotel information** - Name, address, description, and ratings
- **Amenities list** - All available facilities and services
- **Image gallery** - Hotel photos and images
- **Location details** - Address, coordinates, and location information
- **Hotel metadata** - Star rating, chain information, and classifications

## Quick Start

Provide the `hotelId` as a query parameter. Returns complete hotel details including all metadata, amenities, and images.

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
    "/data/hotel": {
      "get": {
        "tags": [
          "Hotels"
        ],
        "summary": "Get the details of a hotel",
        "description": "## Overview\n\nGet comprehensive details about a specific hotel including descriptions, amenities, images, location, and ratings. Perfect for displaying hotel detail pages.\n\n## When to Use\n\n- **Hotel detail pages** - Show complete hotel information\n- **Booking pages** - Display hotel details before booking\n- **Hotel profiles** - Build rich hotel information pages\n- **Content display** - Show descriptions, amenities, and images\n\n## What You Get\n\n- **Complete hotel information** - Name, address, description, and ratings\n- **Amenities list** - All available facilities and services\n- **Image gallery** - Hotel photos and images\n- **Location details** - Address, coordinates, and location information\n- **Hotel metadata** - Star rating, chain information, and classifications\n\n## Quick Start\n\nProvide the `hotelId` as a query parameter. Returns complete hotel details including all metadata, amenities, and images.",
        "security": [
          {
            "apikeyAuth": []
          }
        ],
        "parameters": [
          {
            "name": "hotelId",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Unique ID of a hotel",
            "required": true,
            "example": "lp1897"
          },
          {
            "schema": {
              "type": "number",
              "default": 4,
              "format": "float"
            },
            "in": "query",
            "name": "timeout",
            "description": "request timeout in seconds",
            "example": "1.5"
          },
          {
            "name": "language",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "The language code, indicating in which language the results should be returned. e.g. 'fr'",
            "example": "fr"
          },
          {
            "name": "advancedAccessibilityOnly",
            "schema": {
              "type": "boolean"
            },
            "in": "query",
            "description": "If `true`, accessibility section will be returned",
            "example": true
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
                      "type": "object",
                      "required": [
                        "id",
                        "name"
                      ],
                      "properties": {
                        "id": {
                          "type": "string",
                          "description": "Unique identifier for the hotel."
                        },
                        "name": {
                          "type": "string",
                          "description": "Name of the hotel."
                        },
                        "hotelDescription": {
                          "type": "string",
                          "description": "Detailed description of the hotel."
                        },
                        "markdownDescription": {
                          "type": "string",
                          "description": "Detailed description of the hotel, formatted as Markdown."
                        },
                        "normalizedDescription": {
                          "type": "string",
                          "description": "Normalized plain-text description of the hotel."
                        },
                        "hotelImportantInformation": {
                          "type": "string",
                          "description": "Critical information and important details about the hotel."
                        },
                        "connectivityType": {
                          "type": "string",
                          "enum": [
                            "Pull",
                            "Pull&Push"
                          ],
                          "description": "How the property is connected: `Pull&Push` for Cloud properties connected as Push+Pull, `Pull` otherwise."
                        },
                        "securityDeposit": {
                          "type": "object",
                          "description": "The refundable deposit the property collects from the guest. Only amount, currency and basis are returned.",
                          "properties": {
                            "amount": {
                              "type": "number",
                              "description": "Deposit amount in the currency given.",
                              "example": 150
                            },
                            "currency": {
                              "type": "string",
                              "description": "ISO 4217 code, e.g. USD.",
                              "example": "USD"
                            },
                            "basis": {
                              "type": "string",
                              "description": "How the amount is charged, e.g. per_stay or per_night. Absent when not known.",
                              "example": "per_stay"
                            }
                          }
                        },
                        "state": {
                          "type": "string",
                          "nullable": true,
                          "description": "State or province. 2-letter code for the US.",
                          "example": "UT"
                        },
                        "website": {
                          "type": "string",
                          "nullable": true,
                          "description": "Official website."
                        },
                        "contact": {
                          "type": "object",
                          "description": "Legal and licensing details.",
                          "properties": {
                            "legalEntity": {
                              "type": "string",
                              "nullable": true,
                              "description": "Legal entity name."
                            },
                            "registrationNumber": {
                              "type": "string",
                              "nullable": true,
                              "description": "Business registration number."
                            },
                            "licenceNumbers": {
                              "type": "array",
                              "nullable": true,
                              "items": {
                                "type": "string"
                              },
                              "description": "Licence or permit numbers."
                            }
                          }
                        },
                        "operatingStatus": {
                          "type": "object",
                          "description": "Whether the property is operating.",
                          "properties": {
                            "status": {
                              "type": "string",
                              "nullable": true,
                              "description": "Status.",
                              "enum": [
                                "open",
                                "temporarily_closed",
                                "permanently_closed",
                                "likely_closed",
                                "unknown",
                                null
                              ]
                            },
                            "checkedAt": {
                              "type": "string",
                              "nullable": true,
                              "description": "When the status was checked.",
                              "format": "date-time"
                            }
                          }
                        },
                        "policyAttributes": {
                          "type": "object",
                          "description": "Typed hotel policies and facility flags. Every boolean is true, false or null. null means not stated. Flags derived from the facility list are false when the facility is absent; all flags are null when the hotel has no facilities.",
                          "properties": {
                            "checkIn": {
                              "type": "object",
                              "description": "Check-in window, HH:MM.",
                              "properties": {
                                "from": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "Earliest check-in."
                                },
                                "until": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "Latest check-in."
                                }
                              }
                            },
                            "checkOut": {
                              "type": "object",
                              "description": "Check-out, HH:MM.",
                              "properties": {
                                "until": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "Latest check-out."
                                }
                              }
                            },
                            "minimumCheckInAge": {
                              "type": "integer",
                              "nullable": true,
                              "description": "Minimum age to check in."
                            },
                            "saleCutoff": {
                              "type": "object",
                              "description": "Latest time a booking can be made.",
                              "properties": {
                                "time": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "HH:MM."
                                },
                                "daysBefore": {
                                  "type": "integer",
                                  "nullable": true,
                                  "description": "Days before arrival."
                                }
                              }
                            },
                            "children": {
                              "type": "object",
                              "description": "Children policy.",
                              "properties": {
                                "allowed": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Children allowed."
                                },
                                "infantsAllowed": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Infants allowed."
                                },
                                "cribAvailable": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Crib or cot available."
                                },
                                "cribFee": {
                                  "type": "number",
                                  "nullable": true,
                                  "description": "Crib fee, 0 when free."
                                },
                                "cribAgeMax": {
                                  "type": "integer",
                                  "nullable": true,
                                  "description": "Maximum age for a crib."
                                }
                              }
                            },
                            "pets": {
                              "type": "object",
                              "description": "Pet policy.",
                              "properties": {
                                "allowed": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Pets allowed."
                                },
                                "fee": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "A pet fee applies."
                                },
                                "feeAmount": {
                                  "type": "number",
                                  "nullable": true,
                                  "description": "Pet fee amount."
                                },
                                "feeCurrency": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "ISO 4217 code."
                                },
                                "feeBasis": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "How the fee is charged."
                                },
                                "weightLimitLb": {
                                  "type": "number",
                                  "nullable": true,
                                  "description": "Weight limit in pounds."
                                },
                                "feeRefundable": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Fee is refundable."
                                },
                                "terms": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "Pet policy text."
                                }
                              }
                            },
                            "deposit": {
                              "type": "object",
                              "description": "Security deposit.",
                              "properties": {
                                "required": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Deposit is required."
                                },
                                "amount": {
                                  "type": "number",
                                  "nullable": true,
                                  "description": "Amount."
                                },
                                "currency": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "ISO 4217 code."
                                },
                                "basis": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "per_stay or per_night."
                                },
                                "collectedAt": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "When it is collected, e.g. checkin."
                                },
                                "refundedAt": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "When it is refunded, e.g. checkout."
                                },
                                "method": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "How it is collected, e.g. credit_card."
                                }
                              }
                            },
                            "payment": {
                              "type": "object",
                              "description": "Payment methods at the property.",
                              "properties": {
                                "cardsAccepted": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Cards accepted."
                                },
                                "cashAccepted": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Cash accepted."
                                }
                              }
                            },
                            "wifi": {
                              "type": "object",
                              "description": "WiFi.",
                              "properties": {
                                "inRoom": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "WiFi in guest rooms."
                                },
                                "free": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "WiFi is free."
                                },
                                "feeAmount": {
                                  "type": "number",
                                  "nullable": true,
                                  "description": "WiFi fee."
                                },
                                "feePeriod": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "per_night, per_stay or per_hour."
                                },
                                "freeInPublicAreas": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Free WiFi in public areas."
                                }
                              }
                            },
                            "parking": {
                              "type": "object",
                              "description": "Parking.",
                              "properties": {
                                "selfParking": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Self parking available."
                                },
                                "free": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Parking is free."
                                },
                                "chargeMode": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "Charge mode.",
                                  "enum": [
                                    "free",
                                    "per_day",
                                    "per_hour",
                                    "charges_may_apply",
                                    "charges_apply",
                                    null
                                  ]
                                },
                                "feeAmount": {
                                  "type": "number",
                                  "nullable": true,
                                  "description": "Parking fee."
                                },
                                "feePeriod": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "per_night, per_stay or per_hour."
                                },
                                "location": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "Parking location.",
                                  "enum": [
                                    "on_site",
                                    "offsite",
                                    null
                                  ]
                                },
                                "reservationRequired": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Reservation needed."
                                },
                                "valet": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Valet parking."
                                },
                                "evCharger": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Electric vehicle charging."
                                }
                              }
                            },
                            "facilities": {
                              "type": "object",
                              "description": "Facility flags.",
                              "properties": {
                                "nonSmokingProperty": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Whole property is non-smoking."
                                },
                                "elevator": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Elevator."
                                },
                                "fitnessCenter": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Fitness center."
                                },
                                "fitnessOpen24h": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Fitness center open 24 hours."
                                },
                                "pool": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Pool."
                                },
                                "poolType": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "indoor, outdoor or indoor_and_outdoor."
                                },
                                "poolCurrentlyClosed": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Pool closed today."
                                },
                                "hotTub": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Hot tub."
                                },
                                "spaServices": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Spa services."
                                },
                                "sauna": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Sauna."
                                },
                                "saunaCurrentlyClosed": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Sauna closed today."
                                }
                              }
                            },
                            "foodAndDrink": {
                              "type": "object",
                              "description": "Food and drink.",
                              "properties": {
                                "bar": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Bar on property."
                                },
                                "restaurant": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Restaurant on property."
                                },
                                "restaurantNames": {
                                  "type": "array",
                                  "nullable": true,
                                  "items": {
                                    "type": "string"
                                  },
                                  "description": "Restaurant names."
                                },
                                "roomService": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Room service."
                                },
                                "breakfastAvailable": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Breakfast available."
                                },
                                "breakfastIncluded": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Breakfast included."
                                },
                                "breakfastPrice": {
                                  "type": "object",
                                  "description": "Breakfast price.",
                                  "properties": {
                                    "amount": {
                                      "type": "number",
                                      "nullable": true,
                                      "description": "Amount."
                                    },
                                    "currency": {
                                      "type": "string",
                                      "nullable": true,
                                      "description": "ISO 4217 code."
                                    },
                                    "period": {
                                      "type": "string",
                                      "nullable": true,
                                      "description": "per_night, per_stay or per_hour."
                                    }
                                  }
                                },
                                "breakfastType": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "Breakfast type.",
                                  "enum": [
                                    "continental",
                                    "hot",
                                    "buffet",
                                    "american",
                                    "other",
                                    null
                                  ]
                                }
                              }
                            },
                            "renovation": {
                              "type": "object",
                              "description": "Renovation.",
                              "properties": {
                                "underway": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Renovation underway."
                                },
                                "notes": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "Renovation notes."
                                }
                              }
                            },
                            "resortFee": {
                              "type": "object",
                              "description": "Resort fee.",
                              "properties": {
                                "amount": {
                                  "type": "number",
                                  "nullable": true,
                                  "description": "Amount."
                                },
                                "currency": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "ISO 4217 code."
                                },
                                "period": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "per_night, per_stay or per_hour."
                                }
                              }
                            },
                            "cancellationTerms": {
                              "type": "string",
                              "nullable": true,
                              "description": "Property cancellation terms."
                            },
                            "houseRules": {
                              "type": "array",
                              "nullable": true,
                              "items": {
                                "type": "string"
                              },
                              "description": "House rules."
                            },
                            "facilityClosures": {
                              "type": "array",
                              "nullable": true,
                              "description": "Facility closures with ISO dates.",
                              "items": {
                                "type": "object",
                                "description": "Closure.",
                                "properties": {
                                  "facility": {
                                    "type": "string"
                                  },
                                  "from": {
                                    "type": "string",
                                    "format": "date"
                                  },
                                  "until": {
                                    "type": "string",
                                    "format": "date"
                                  }
                                }
                              }
                            },
                            "roomAmenitiesAllRooms": {
                              "type": "object",
                              "description": "Room amenities true only when every room has them; false when a room states it lacks them.",
                              "properties": {
                                "television": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "TV in the room."
                                },
                                "televisionSize": {
                                  "type": "string",
                                  "nullable": true,
                                  "description": "TV size, e.g. 40-inch."
                                },
                                "airConditioner": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Air conditioning."
                                },
                                "hairDryer": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Hair dryer."
                                },
                                "iron": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Iron or ironing facilities."
                                },
                                "hangers": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Hangers."
                                },
                                "coffeeMaker": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Coffee or tea maker."
                                },
                                "crib": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Crib or cot available."
                                },
                                "laptopFriendlyWorkplace": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Desk or laptop-friendly workspace."
                                },
                                "kitchen": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Kitchen or kitchenette."
                                },
                                "smokeDetector": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Smoke detector."
                                },
                                "carbonMonoxideDetector": {
                                  "type": "boolean",
                                  "nullable": true,
                                  "description": "Carbon monoxide detector."
                                }
                              }
                            }
                          }
                        },
                        "checkinCheckoutTimes": {
                          "type": "object",
                          "description": "Check-in and check-out timing details for the hotel.",
                          "properties": {
                            "checkout": {
                              "type": "string",
                              "description": "Standard check-out time for guests."
                            },
                            "checkin_start": {
                              "type": "string",
                              "description": "Earliest time a guest can check in."
                            },
                            "checkin_end": {
                              "type": "string",
                              "description": "Latest time a guest can check in."
                            },
                            "instructions": {
                              "type": "array",
                              "items": {
                                "type": "object",
                                "properties": {
                                  "id": {
                                    "type": "integer",
                                    "description": "Identifier of the instruction, when available."
                                  },
                                  "instruction": {
                                    "type": "string",
                                    "description": "Check-in or check-out instruction text."
                                  }
                                },
                                "required": [
                                  "instruction"
                                ]
                              },
                              "description": "Check-in and check-out instructions, always returned as an array of objects."
                            },
                            "special_instructions": {
                              "type": "string",
                              "description": "Special instructions for check-in and check-out."
                            }
                          }
                        },
                        "hotelImages": {
                          "type": "array",
                          "description": "List of images representing the hotel.",
                          "items": {
                            "type": "object",
                            "description": "Details of an individual hotel image.",
                            "properties": {
                              "url": {
                                "type": "string",
                                "description": "URL of the hotel image."
                              },
                              "urlHd": {
                                "type": "string",
                                "description": "High definition URL of the hotel image."
                              },
                              "caption": {
                                "type": "string",
                                "description": "Caption or description for the image."
                              },
                              "order": {
                                "type": "integer",
                                "description": "Display order of the image."
                              },
                              "defaultImage": {
                                "type": "boolean",
                                "description": "Indicates if this image is the default for the hotel."
                              }
                            }
                          }
                        },
                        "main_photo": {
                          "type": "string",
                          "description": "URL of the main photo for the hotel."
                        },
                        "thumbnail": {
                          "type": "string",
                          "description": "URL of the hotel's thumbnail image."
                        },
                        "videoUrl": {
                          "type": "string",
                          "description": "URL of the hotel's video."
                        },
                        "country": {
                          "type": "string",
                          "description": "Country where the hotel is located."
                        },
                        "city": {
                          "type": "string",
                          "description": "City where the hotel is located."
                        },
                        "starRating": {
                          "type": "integer",
                          "description": "Star rating of the hotel."
                        },
                        "location": {
                          "type": "object",
                          "description": "Geographical coordinates of the hotel.",
                          "properties": {
                            "latitude": {
                              "type": "number",
                              "description": "Latitude coordinate of the hotel."
                            },
                            "longitude": {
                              "type": "number",
                              "description": "Longitude coordinate of the hotel."
                            }
                          }
                        },
                        "address": {
                          "type": "string",
                          "description": "Physical address of the hotel."
                        },
                        "hotelFacilities": {
                          "type": "array",
                          "description": "List of facilities offered by the hotel.",
                          "items": {
                            "type": "string",
                            "description": "A facility available at the hotel."
                          }
                        },
                        "chain": {
                          "type": "string",
                          "description": "Name of the hotel chain, if applicable."
                        },
                        "facilities": {
                          "type": "array",
                          "description": "Detailed list of facilities provided by the hotel.",
                          "items": {
                            "type": "object",
                            "description": "Information about a specific facility.",
                            "properties": {
                              "facilityId": {
                                "type": "integer",
                                "description": "Unique identifier for the facility."
                              },
                              "name": {
                                "type": "string",
                                "description": "Name of the facility."
                              }
                            }
                          }
                        },
                        "rooms": {
                          "type": "array",
                          "description": "List of room objects available in the hotel.",
                          "items": {
                            "type": "object",
                            "description": "Details of an individual room in the hotel.",
                            "properties": {
                              "id": {
                                "type": "integer",
                                "description": "Unique identifier for the room."
                              },
                              "roomName": {
                                "type": "string",
                                "description": "Name or type designation of the room."
                              },
                              "roomTitle": {
                                "type": "string",
                                "description": "Display title of the room."
                              },
                              "description": {
                                "type": "string",
                                "description": "Detailed description of the room and its features."
                              },
                              "markdownDescription": {
                                "type": "string",
                                "description": "Description of the room and its features, formatted as Markdown."
                              },
                              "normalizedDescription": {
                                "type": "string",
                                "description": "Normalized plain-text description of the room and its features."
                              },
                              "roomSizeSquare": {
                                "type": "number",
                                "description": "Size of the room in square meters."
                              },
                              "roomSizeUnit": {
                                "type": "string",
                                "description": "Unit of measurement for room size (e.g., 'm2')."
                              },
                              "hotelId": {
                                "type": "string",
                                "description": "Identifier of the hotel this room belongs to."
                              },
                              "maxAdults": {
                                "type": "integer",
                                "description": "Maximum number of adults that can be accommodated."
                              },
                              "maxChildren": {
                                "type": "integer",
                                "description": "Maximum number of children that can be accommodated."
                              },
                              "maxOccupancy": {
                                "type": "integer",
                                "description": "Overall maximum occupancy of the room."
                              },
                              "officialSiteRoomName": {
                                "type": "string",
                                "nullable": true,
                                "description": "Room name as shown on the property's own website. Not for display as a title."
                              },
                              "attributes": {
                                "type": "object",
                                "description": "Room amenity flags. Every boolean is true, false or null. null means not stated. false only when the room states it lacks the amenity.",
                                "properties": {
                                  "television": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "TV in the room."
                                  },
                                  "televisionSize": {
                                    "type": "string",
                                    "nullable": true,
                                    "description": "TV size, e.g. 40-inch."
                                  },
                                  "airConditioner": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Air conditioning."
                                  },
                                  "hairDryer": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Hair dryer."
                                  },
                                  "iron": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Iron or ironing facilities."
                                  },
                                  "hangers": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Hangers."
                                  },
                                  "coffeeMaker": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Coffee or tea maker."
                                  },
                                  "crib": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Crib or cot available."
                                  },
                                  "laptopFriendlyWorkplace": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Desk or laptop-friendly workspace."
                                  },
                                  "kitchen": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Kitchen or kitchenette."
                                  },
                                  "smokeDetector": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Smoke detector."
                                  },
                                  "carbonMonoxideDetector": {
                                    "type": "boolean",
                                    "nullable": true,
                                    "description": "Carbon monoxide detector."
                                  }
                                }
                              },
                              "ranking": {
                                "type": "integer",
                                "minimum": 1,
                                "maximum": 5,
                                "description": "Popularity rank of the room, from 1 (low) to 5 (high).",
                                "example": 3
                              },
                              "bedTypes": {
                                "type": "array",
                                "description": "List of bed configurations available in the room.",
                                "items": {
                                  "type": "object",
                                  "description": "Details of a specific bed type in the room.",
                                  "properties": {
                                    "quantity": {
                                      "type": "integer",
                                      "description": "Number of beds of this type available in the room."
                                    },
                                    "bedType": {
                                      "type": "string",
                                      "description": "Type of the bed (e.g., 'King size', 'Double bed')."
                                    },
                                    "bedSize": {
                                      "type": "string",
                                      "description": "Dimensions or size description of the bed."
                                    },
                                    "Id": {
                                      "type": "integer",
                                      "description": "Unique identifier for the bed type."
                                    }
                                  }
                                }
                              },
                              "roomAmenities": {
                                "type": "array",
                                "description": "List of amenities provided in the room.",
                                "items": {
                                  "type": "object",
                                  "description": "Details of a specific room amenity.",
                                  "properties": {
                                    "amenitiesId": {
                                      "type": "integer",
                                      "description": "Unique identifier for the amenity."
                                    },
                                    "name": {
                                      "type": "string",
                                      "description": "Name of the amenity."
                                    },
                                    "sort": {
                                      "type": "integer",
                                      "description": "Sort order for displaying the amenity."
                                    }
                                  }
                                }
                              },
                              "photos": {
                                "type": "array",
                                "description": "List of photos associated with the room.",
                                "items": {
                                  "type": "object",
                                  "description": "Details of a room photo.",
                                  "properties": {
                                    "url": {
                                      "type": "string",
                                      "description": "URL of the room photo."
                                    },
                                    "imageDescription": {
                                      "type": "string",
                                      "description": "Description or caption for the photo."
                                    },
                                    "imageClass1": {
                                      "type": "string",
                                      "description": "Additional classification or tag for the image."
                                    },
                                    "imageClass2": {
                                      "type": "string",
                                      "description": "Secondary classification or tag for the image."
                                    },
                                    "failoverPhoto": {
                                      "type": "string",
                                      "description": "Backup URL for the photo in case the primary URL fails."
                                    },
                                    "mainPhoto": {
                                      "type": "boolean",
                                      "description": "Indicates if this is the main photo for the room."
                                    },
                                    "score": {
                                      "type": "number",
                                      "description": "Relevance score or ranking for the photo."
                                    },
                                    "classId": {
                                      "type": "integer",
                                      "description": "Identifier for the photo's classification."
                                    },
                                    "classOrder": {
                                      "type": "integer",
                                      "description": "Order of appearance for the photo based on its classification."
                                    },
                                    "hd_url": {
                                      "type": "string",
                                      "description": "High definition URL of the room photo."
                                    }
                                  }
                                }
                              },
                              "bedRelation": {
                                "type": "string",
                                "enum": [
                                  "NONE",
                                  "AND",
                                  "OR",
                                  "COMPLEX"
                                ],
                                "description": "How the entries in `bedTypes` relate to one another.\n\n- `NONE` - no relation applies, the room has a single bed configuration (or none is published).\n- `AND` - every listed bed is present in the room at the same time, e.g. a King **and** a sofa bed.\n- `OR` - the listed beds are alternatives, the room is made up with one of them, e.g. a King **or** two Queens.\n- `COMPLEX` - the setup does not reduce to a single `AND` or `OR`, e.g. a King **or** two Queens, **and** a sofa bed.",
                                "example": "OR"
                              }
                            }
                          }
                        },
                        "accessibility": {
                          "type": "object",
                          "description": "Accessibility information and certifications for the hotel.",
                          "properties": {
                            "certificateId": {
                              "type": "string",
                              "description": "Identifier for the accessibility certificate."
                            },
                            "certificateUrl": {
                              "type": "string",
                              "description": "URL where the accessibility certificate can be viewed."
                            },
                            "certificateHtml": {
                              "type": "string",
                              "description": "HTML content of the accessibility certificate."
                            },
                            "attributes": {
                              "type": "object",
                              "description": "Detailed accessibility attributes for various areas of the hotel.",
                              "properties": {
                                "publicArea": {
                                  "type": "object",
                                  "description": "Accessibility details for public areas.",
                                  "properties": {
                                    "hillLocated": {
                                      "type": "boolean",
                                      "description": "Indicates if the public area is located on a hill."
                                    },
                                    "isRamp": {
                                      "type": "boolean",
                                      "description": "Indicates if a ramp is available in the public area."
                                    },
                                    "rampAngle": {
                                      "type": "number",
                                      "description": "Angle of the ramp in the public area."
                                    },
                                    "rampLength": {
                                      "type": "number",
                                      "description": "Length of the ramp in the public area."
                                    },
                                    "entranceType": {
                                      "type": "string",
                                      "description": "Type of entrance provided in the public area."
                                    },
                                    "entranceDoorWidth": {
                                      "type": "number",
                                      "description": "Width of the entrance door."
                                    },
                                    "entranceLeveled": {
                                      "type": "boolean",
                                      "description": "Indicates if the entrance is level with the ground."
                                    },
                                    "distanceFromTheElevatorToTheAccessibleRoom": {
                                      "type": "number",
                                      "description": "Distance between the elevator and an accessible room."
                                    },
                                    "roomMaxGuestsNumber": {
                                      "type": "integer",
                                      "description": "Maximum number of guests allowed in an accessible room."
                                    },
                                    "petFriendly": {
                                      "type": "string",
                                      "description": "Indicates if the accessible area is pet-friendly."
                                    }
                                  }
                                },
                                "receptionAndRestaurant": {
                                  "type": "object",
                                  "description": "Accessibility details for the reception and restaurant areas.",
                                  "properties": {
                                    "receptionHearingAccessible": {
                                      "type": "boolean",
                                      "description": "Indicates if the reception area is accessible for the hearing impaired."
                                    },
                                    "receptionWheelchairAccessible": {
                                      "type": "boolean",
                                      "description": "Indicates if the reception area is wheelchair accessible."
                                    },
                                    "restaurantWheelchairAccessible": {
                                      "type": "boolean",
                                      "description": "Indicates if the restaurant is wheelchair accessible."
                                    }
                                  }
                                },
                                "elevator": {
                                  "type": "object",
                                  "description": "Accessibility features for elevators.",
                                  "properties": {
                                    "elevatorAudioSignals": {
                                      "type": "boolean",
                                      "description": "Indicates if the elevator has audio signals."
                                    },
                                    "elevatorBrailleNumbers": {
                                      "type": "boolean",
                                      "description": "Indicates if the elevator displays braille numbers."
                                    },
                                    "elevatorContrastingButtons": {
                                      "type": "boolean",
                                      "description": "Indicates if the elevator buttons use contrasting colors for visibility."
                                    },
                                    "elevatorVisualSigns": {
                                      "type": "boolean",
                                      "description": "Indicates if visual indicators are present in the elevator."
                                    },
                                    "liftsAccessible": {
                                      "type": "boolean",
                                      "description": "Indicates if the lifts are accessible to all users."
                                    }
                                  }
                                },
                                "room": {
                                  "type": "object",
                                  "description": "Accessibility features provided within hotel rooms.",
                                  "properties": {
                                    "brailleRoomNumbers": {
                                      "type": "boolean",
                                      "description": "Indicates if room numbers are available in braille."
                                    },
                                    "noticeableRoomNumber": {
                                      "type": "boolean",
                                      "description": "Indicates if the room number is prominently displayed."
                                    },
                                    "roomDoorPeephole": {
                                      "type": "boolean",
                                      "description": "Indicates if the room door is equipped with a peephole."
                                    },
                                    "roomKnockSign": {
                                      "type": "boolean",
                                      "description": "Indicates if there is a knock sign on the room door."
                                    },
                                    "serviceFromTv": {
                                      "type": "boolean",
                                      "description": "Indicates if in-room TV service is available."
                                    },
                                    "hearingKit": {
                                      "type": "boolean",
                                      "description": "Indicates if a hearing kit is provided in the room."
                                    },
                                    "tvAlarmClock": {
                                      "type": "boolean",
                                      "description": "Indicates if the room includes a TV alarm clock."
                                    },
                                    "accessibleRoomConnectingDoors": {
                                      "type": "boolean",
                                      "description": "Indicates if accessible rooms have connecting doors."
                                    },
                                    "ttyHearingSpeechImpaired": {
                                      "type": "boolean",
                                      "description": "Indicates if the room is equipped with TTY facilities for those who are hearing or speech impaired."
                                    }
                                  }
                                },
                                "toiletAndShower": {
                                  "type": "object",
                                  "description": "Accessibility features for toilet and shower facilities.",
                                  "properties": {
                                    "toiletBathroomDoorsIndicator": {
                                      "type": "boolean",
                                      "description": "Indicates if the bathroom door is clearly marked."
                                    },
                                    "toiletGrabbingHandles": {
                                      "type": "boolean",
                                      "description": "Indicates if grabbing handles are available in the toilet area."
                                    },
                                    "adjustableShowerHead": {
                                      "type": "boolean",
                                      "description": "Indicates if the shower head is adjustable."
                                    },
                                    "bathroomBathtub": {
                                      "type": "boolean",
                                      "description": "Indicates if a bathtub is available in the bathroom."
                                    },
                                    "bathroomEmergencyCord": {
                                      "type": "boolean",
                                      "description": "Indicates if an emergency cord is present in the bathroom."
                                    },
                                    "bathtubGrabbingHandles": {
                                      "type": "boolean",
                                      "description": "Indicates if the bathtub is equipped with grabbing handles."
                                    },
                                    "cabinUnderSink": {
                                      "type": "boolean",
                                      "description": "Indicates if there is a cabinet or storage space under the sink."
                                    },
                                    "showerChair": {
                                      "type": "string",
                                      "description": "Specifies the type of shower chair available, if any."
                                    },
                                    "showerLevelFloor": {
                                      "type": "boolean",
                                      "description": "Indicates if the shower area has a level floor."
                                    }
                                  }
                                },
                                "stairs": {
                                  "type": "object",
                                  "description": "Accessibility details for staircases.",
                                  "properties": {
                                    "isHandrails": {
                                      "type": "boolean",
                                      "description": "Indicates if handrails are installed on the stairs."
                                    },
                                    "isStairMarked": {
                                      "type": "boolean",
                                      "description": "Indicates if the stairs are clearly marked for safety."
                                    }
                                  }
                                },
                                "poolAndSpa": {
                                  "type": "object",
                                  "description": "Accessibility features for pool and spa areas.",
                                  "properties": {
                                    "isPoolHandrails": {
                                      "type": "boolean",
                                      "description": "Indicates if handrails are available in the pool area."
                                    },
                                    "liftInPool": {
                                      "type": "boolean",
                                      "description": "Indicates if there is a lift available in the pool."
                                    },
                                    "spaWheelchairAccessible": {
                                      "type": "boolean",
                                      "description": "Indicates if the spa area is designed to be wheelchair accessible."
                                    }
                                  }
                                },
                                "parking": {
                                  "type": "object",
                                  "description": "Accessibility details for the parking area.",
                                  "properties": {
                                    "parkingWheelchairAccessible": {
                                      "type": "boolean",
                                      "description": "Indicates if the parking area is accessible for wheelchair users."
                                    }
                                  }
                                },
                                "publicToilets": {
                                  "type": "object",
                                  "description": "Accessibility features for public toilet facilities.",
                                  "properties": {
                                    "publicToiletsEmergencyCord": {
                                      "type": "boolean",
                                      "description": "Indicates if an emergency cord is available in public toilets."
                                    },
                                    "publicToiletsGrabbingHandles": {
                                      "type": "boolean",
                                      "description": "Indicates if grabbing handles are provided in public toilets."
                                    },
                                    "publicToiletsVisualIndicators": {
                                      "type": "boolean",
                                      "description": "Indicates if visual indicators are present in public toilet facilities."
                                    }
                                  }
                                }
                              }
                            },
                            "totalDisabilityScore": {
                              "type": "number",
                              "description": "Overall score representing the hotel's disability accessibility."
                            },
                            "disabilities": {
                              "type": "object",
                              "description": "Flags indicating specific disability accommodations available at the hotel.",
                              "properties": {
                                "crouchesCaneImpaired": {
                                  "type": "boolean",
                                  "description": "Indicates if accommodations are available for individuals using a cane."
                                },
                                "elderlyImpaired": {
                                  "type": "boolean",
                                  "description": "Indicates if the facility is adapted for the elderly."
                                },
                                "electricWheelchair": {
                                  "type": "boolean",
                                  "description": "Indicates if electric wheelchair accessibility is provided."
                                },
                                "hearingImpaired": {
                                  "type": "boolean",
                                  "description": "Indicates if the facility is adapted for those with hearing impairments."
                                },
                                "manualWheelchair": {
                                  "type": "boolean",
                                  "description": "Indicates if manual wheelchair accessibility is provided."
                                },
                                "visuallyImpaired": {
                                  "type": "boolean",
                                  "description": "Indicates if accommodations are available for the visually impaired."
                                },
                                "wheelchairOrScooterImpaired": {
                                  "type": "boolean",
                                  "description": "Indicates if the facility accommodates wheelchair or scooter users."
                                }
                              }
                            },
                            "disabilityScores": {
                              "type": "object",
                              "description": "Accessibility scores for various disability categories.",
                              "properties": {
                                "crouchesCaneImpaired": {
                                  "type": "integer",
                                  "description": "Accessibility score for individuals using a cane."
                                },
                                "elderlyImpaired": {
                                  "type": "integer",
                                  "description": "Accessibility score for elderly guests."
                                },
                                "electricWheelchair": {
                                  "type": "integer",
                                  "description": "Accessibility score for electric wheelchair users."
                                },
                                "hearingImpaired": {
                                  "type": "integer",
                                  "description": "Accessibility score for hearing impaired individuals."
                                },
                                "manualWheelchair": {
                                  "type": "integer",
                                  "description": "Accessibility score for manual wheelchair users."
                                },
                                "visuallyImpaired": {
                                  "type": "integer",
                                  "description": "Accessibility score for visually impaired individuals."
                                },
                                "wheelchairOrScooterImpaired": {
                                  "type": "integer",
                                  "description": "Accessibility score for wheelchair or scooter users."
                                }
                              }
                            }
                          }
                        },
                        "phone": {
                          "type": "string",
                          "description": "Contact phone number for the hotel."
                        },
                        "poi": {
                          "type": "array",
                          "description": "Points of interest near the hotel with their category, distance in kilometres, and importance.",
                          "items": {
                            "type": "object",
                            "properties": {
                              "name": {
                                "type": "string",
                                "description": "Name of the point of interest.",
                                "example": "Edinburgh Castle"
                              },
                              "category": {
                                "type": "string",
                                "description": "Category of the point of interest.",
                                "example": "landmark"
                              },
                              "distanceKm": {
                                "type": "number",
                                "description": "Distance from the hotel to the point of interest, in kilometres.",
                                "example": 0.7
                              },
                              "importance": {
                                "type": "string",
                                "description": "Relative importance of the point of interest.",
                                "example": "iconic"
                              }
                            }
                          }
                        },
                        "fax": {
                          "type": "string",
                          "description": "Fax number for the hotel."
                        },
                        "email": {
                          "type": "string",
                          "description": "Contact email address for the hotel."
                        },
                        "hotelType": {
                          "type": "string",
                          "description": "Type or category of the hotel."
                        },
                        "hotelTypeId": {
                          "type": "number",
                          "description": "Identifier for the hotel type."
                        },
                        "chainId": {
                          "type": "number",
                          "description": "Identifier for the hotel chain."
                        },
                        "airportCode": {
                          "type": "string",
                          "description": "IATA code of the nearest airport."
                        },
                        "rating": {
                          "type": "number",
                          "description": "Overall rating of the hotel."
                        },
                        "reviewCount": {
                          "type": "integer",
                          "description": "Total number of reviews received for the hotel."
                        },
                        "rohId": {
                          "type": "integer",
                          "description": "Room of House ID - unique identifier for the hotel property."
                        },
                        "parking": {
                          "type": "boolean",
                          "nullable": true,
                          "description": "Indicates whether parking is available (nullable)."
                        },
                        "groupRoomMin": {
                          "type": "number",
                          "nullable": true,
                          "description": "Minimum number of rooms required for group bookings (nullable)."
                        },
                        "childAllowed": {
                          "type": "boolean",
                          "nullable": true,
                          "description": "Indicates if children are allowed at the hotel (nullable)."
                        },
                        "petsAllowed": {
                          "type": "boolean",
                          "nullable": true,
                          "description": "Indicates if pets are allowed at the hotel (nullable)."
                        },
                        "policies": {
                          "type": "array",
                          "description": "List of policies and guidelines applicable to the hotel.",
                          "items": {
                            "type": "object",
                            "description": "An individual policy object.",
                            "properties": {
                              "policy_type": {
                                "type": "string",
                                "description": "Specifies the category or classification of the policy."
                              },
                              "name": {
                                "type": "string",
                                "description": "The official title or identifier of the policy, providing a concise summary of its purpose."
                              },
                              "description": {
                                "type": "string",
                                "description": "A comprehensive explanation of the policy, outlining its terms, conditions, and operational details."
                              },
                              "child_allowed": {
                                "type": "string",
                                "description": "Details regarding the hotel's policy on children, including any age restrictions, complimentary services, or special provisions."
                              },
                              "pets_allowed": {
                                "type": "string",
                                "description": "Describes the pet policy, including whether pets are allowed, any applicable restrictions or fees, and specific guidelines for pet owners."
                              },
                              "parking": {
                                "type": "string",
                                "description": "Provides information on parking facilities, including availability, any associated fees, and usage guidelines."
                              }
                            }
                          }
                        },
                        "sentiment_analysis": {
                          "type": "object",
                          "description": "Sentiment analysis data derived from guest reviews.",
                          "properties": {
                            "cons": {
                              "type": "array",
                              "description": "List of negative feedback points from guest reviews.",
                              "items": {
                                "type": "string"
                              }
                            },
                            "pros": {
                              "type": "array",
                              "description": "List of positive feedback points from guest reviews.",
                              "items": {
                                "type": "string"
                              }
                            },
                            "categories": {
                              "type": "array",
                              "description": "Detailed sentiment analysis categorized by aspects such as cleanliness, service, etc.",
                              "items": {
                                "type": "object",
                                "description": "A sentiment category including its name, rating, and a detailed description.",
                                "properties": {
                                  "name": {
                                    "type": "string",
                                    "description": "Name of the sentiment category (e.g., Cleanliness, Service)."
                                  },
                                  "rating": {
                                    "type": "number",
                                    "description": "Numerical rating for this sentiment category."
                                  },
                                  "description": {
                                    "type": "string",
                                    "description": "Detailed description of guest sentiment for this category."
                                  }
                                }
                              }
                            }
                          }
                        },
                        "sentiment_updated_at": {
                          "type": "string",
                          "description": "Timestamp indicating when the sentiment analysis was last updated."
                        },
                        "deletedAt": {
                          "type": "string",
                          "description": "Timestamp indicating when hotel is deleted in our system"
                        }
                      }
                    }
                  }
                },
                "examples": {
                  "hotel lp1897": {
                    "value": {
                      "data": {
                        "id": "lp1897",
                        "name": "The Manhattan at Times Square",
                        "hotelDescription": "<p><strong>Central Manhattan Location</strong><br>The Manhattan at Times Square is perfectly situated in the heart of Manhattan, just steps away from iconic landmarks like Times Square and Rockefeller Centre.</p><p><strong>Comfortable Accommodations</strong><br>Each room at The Manhattan at Times Square is equipped with a work desk, and select rooms boast stunning skyline views and a cozy seating area for relaxation.</p><p><strong>Convenient Amenities</strong><br>Guests can enjoy a range of amenities at The Manhattan at Times Square, including a fitness centre, a business centre with fax and photocopying services, and attentive concierge services.</p><p>Book your stay at The Manhattan at Times Square to experience the best of New York City right at your doorstep.</p>",
                        "hotelImportantInformation": "Guests are required to show a photo identification and credit card upon check-in. Please note that all Special Requests are subject to availability and additional charges may apply.\nIn response to Coronavirus (COVID-19), additional safety and sanitation measures are in effect at this property.\nPlease inform The Manhattan at Times Square in advance of your expected arrival time. You can use the Special Requests box when booking, or contact the property directly with the contact details provided in your confirmation.\nA damage deposit of USD 50 is required on arrival. This will be collected by credit card. You should be reimbursed on check-out. Your deposit will be refunded in full via credit card, subject to an inspection of the property.\nPlease note that the Manhattan at Times Square Hotel has a mandatory Facilities Fee of $43.61 per room per night provides guests with the following inclusive services, amenities and added value offerings:\n\nAll local, long distance and international phone calls\nPremium high-speed wireless internet access for unlimited devices\nAn eco-friendly, collapsible 16 oz. water bottle to use at the hotel's water filtration stations\nUnlimited use of our Business Center\nUnlimited use of our 24/7 Fitness Center with treadmills, bikes, elliptical machines and free weights\n25% discount on hop on/hop off bus tour with provided QR code\n*The Facilities Fee does not include service gratuity",
                        "checkinCheckoutTimes": {
                          "checkout": "11:00 AM",
                          "checkin_start": "04:00 PM",
                          "checkin_end": "12:00 AM",
                          "instructions": [
                            {
                              "id": 10397,
                              "instruction": "Government-issued photo identification and a credit card, debit card, or cash deposit may be required at check-in for incidental charges"
                            }
                          ],
                          "special_instructions": ""
                        },
                        "hotelImages": [
                          {
                            "url": "https://static.cupid.travel/hotels/524489007.jpg",
                            "urlHd": "https://static.cupid.travel/hotels/524489007.jpg",
                            "caption": "",
                            "order": 0,
                            "defaultImage": true
                          },
                          {
                            "url": "https://static.cupid.travel/hotels/333925043.jpg",
                            "urlHd": "https://static.cupid.travel/hotels/333925043.jpg",
                            "caption": "",
                            "order": 0,
                            "defaultImage": false
                          },
                          {
                            "url": "https://static.cupid.travel/hotels/40322143.jpg",
                            "urlHd": "https://static.cupid.travel/hotels/40322143.jpg",
                            "caption": "",
                            "order": 0,
                            "defaultImage": false
                          },
                          {
                            "url": "https://static.cupid.travel/hotels/333925055.jpg",
                            "urlHd": "https://static.cupid.travel/hotels/333925055.jpg",
                            "caption": "",
                            "order": 0,
                            "defaultImage": false
                          }
                        ],
                        "main_photo": "https://static.cupid.travel/hotels/524489007.jpg",
                        "thumbnail": "https://static.cupid.travel/hotels/thumbnail/524489007.jpg",
                        "videoUrl": "https://static.nuitee.cloud/videos/lpf82e2.mp4",
                        "country": "us",
                        "city": "New York",
                        "starRating": 4,
                        "location": {
                          "latitude": 40.76217,
                          "longitude": -73.98306
                        },
                        "address": "790 7th Avenue",
                        "hotelFacilities": [
                          "Fitness facilities (surcharge)",
                          "Non-smoking rooms",
                          "Air conditioning",
                          "Family rooms",
                          "Pets allowed",
                          "Lift / Elevator",
                          "Luggage storage",
                          "Express check-in/check-out",
                          "Safety deposit box",
                          "Fax/photocopying",
                          "Soundproof rooms",
                          "Facilities for disabled guests",
                          "Ironing service",
                          "Non-smoking throughout",
                          "Private check-in/check-out",
                          "Staff adhere to local safety protocols",
                          "Guest rooms disinfected between stays",
                          "Cleaning standards that are effective against Coronavirus",
                          "Physical distancing rules followed",
                          "Hand sanitizer in guest room and key areas",
                          "Cashless payment available",
                          "Fire extinguishers",
                          "First aid kit available",
                          "Sanitized tableware & silverware",
                          "Contactless check-in/check-out",
                          "Invoice provided",
                          "Guests can opt-out any cleaning services during stay",
                          "Smoke alarms",
                          "Shared stationery like menus, pens are removed",
                          "Guest room sealed after cleaning",
                          "Property cleaned by professional cleaning companies",
                          "Physical distancing in dining areas",
                          "Key access",
                          "Food delivered to guest room",
                          "CCTV outside property",
                          "Process in place to check health of guests",
                          "Screens / Barriers between staff and guests for safety",
                          "Face masks for guests available",
                          "24-hour security",
                          "Delivered food - securely covered",
                          "Security alarm",
                          "Key card access",
                          "ATM/banking",
                          "Shopping on site",
                          "Television in common areas",
                          "Front desk (limited hours)",
                          "Express check-in",
                          "Free wired internet",
                          "Fitness facilities (surcharge)",
                          "Internet access",
                          "Housekeeping on request",
                          "Covered parking",
                          "Secured parking",
                          "Garage"
                        ],
                        "chain": "Not Available",
                        "facilities": [
                          {
                            "facilityId": 6848,
                            "name": "Fitness facilities (surcharge)"
                          },
                          {
                            "facilityId": 16,
                            "name": "Non-smoking rooms"
                          },
                          {
                            "facilityId": 109,
                            "name": "Air conditioning"
                          },
                          {
                            "facilityId": 28,
                            "name": "Family rooms"
                          },
                          {
                            "facilityId": 4,
                            "name": "Pets allowed"
                          },
                          {
                            "facilityId": 48,
                            "name": "Lift / Elevator"
                          },
                          {
                            "facilityId": 91,
                            "name": "Luggage storage"
                          },
                          {
                            "facilityId": 49,
                            "name": "Express check-in/check-out"
                          },
                          {
                            "facilityId": 51,
                            "name": "Safety deposit box"
                          },
                          {
                            "facilityId": 81,
                            "name": "Fax/photocopying"
                          },
                          {
                            "facilityId": 64,
                            "name": "Soundproof rooms"
                          },
                          {
                            "facilityId": 25,
                            "name": "Facilities for disabled guests"
                          },
                          {
                            "facilityId": 44,
                            "name": "Ironing service"
                          },
                          {
                            "facilityId": 108,
                            "name": "Non-smoking throughout"
                          },
                          {
                            "facilityId": 127,
                            "name": "Private check-in/check-out"
                          },
                          {
                            "facilityId": 455,
                            "name": "Staff adhere to local safety protocols"
                          },
                          {
                            "facilityId": 451,
                            "name": "Guest rooms disinfected between stays"
                          },
                          {
                            "facilityId": 449,
                            "name": "Cleaning standards that are effective against Coronavirus"
                          },
                          {
                            "facilityId": 462,
                            "name": "Physical distancing rules followed"
                          },
                          {
                            "facilityId": 457,
                            "name": "Hand sanitizer in guest room and key areas"
                          },
                          {
                            "facilityId": 461,
                            "name": "Cashless payment available"
                          },
                          {
                            "facilityId": 425,
                            "name": "Fire extinguishers"
                          },
                          {
                            "facilityId": 459,
                            "name": "First aid kit available"
                          },
                          {
                            "facilityId": 467,
                            "name": "Sanitized tableware & silverware"
                          },
                          {
                            "facilityId": 460,
                            "name": "Contactless check-in/check-out"
                          },
                          {
                            "facilityId": 465,
                            "name": "Invoice provided"
                          },
                          {
                            "facilityId": 468,
                            "name": "Guests can opt-out any cleaning services during stay"
                          },
                          {
                            "facilityId": 422,
                            "name": "Smoke alarms"
                          },
                          {
                            "facilityId": 456,
                            "name": "Shared stationery like menus, pens are removed"
                          },
                          {
                            "facilityId": 452,
                            "name": "Guest room sealed after cleaning"
                          },
                          {
                            "facilityId": 466,
                            "name": "Property cleaned by professional cleaning companies"
                          },
                          {
                            "facilityId": 453,
                            "name": "Physical distancing in dining areas"
                          },
                          {
                            "facilityId": 419,
                            "name": "Key access"
                          },
                          {
                            "facilityId": 454,
                            "name": "Food delivered to guest room"
                          },
                          {
                            "facilityId": 424,
                            "name": "CCTV outside property"
                          },
                          {
                            "facilityId": 458,
                            "name": "Process in place to check health of guests"
                          },
                          {
                            "facilityId": 464,
                            "name": "Screens / Barriers between staff and guests for safety"
                          },
                          {
                            "facilityId": 488,
                            "name": "Face masks for guests available"
                          },
                          {
                            "facilityId": 418,
                            "name": "24-hour security"
                          },
                          {
                            "facilityId": 485,
                            "name": "Delivered food - securely covered"
                          },
                          {
                            "facilityId": 421,
                            "name": "Security alarm"
                          },
                          {
                            "facilityId": 420,
                            "name": "Key card access"
                          },
                          {
                            "facilityId": 497,
                            "name": "ATM/banking"
                          },
                          {
                            "facilityId": 500,
                            "name": "Shopping on site"
                          },
                          {
                            "facilityId": 521,
                            "name": "Television in common areas"
                          },
                          {
                            "facilityId": 540,
                            "name": "Front desk (limited hours)"
                          },
                          {
                            "facilityId": 568,
                            "name": "Express check-in"
                          },
                          {
                            "facilityId": 584,
                            "name": "Free wired internet"
                          },
                          {
                            "facilityId": 757,
                            "name": "Fitness facilities (surcharge)"
                          },
                          {
                            "facilityId": 761,
                            "name": "Internet access"
                          },
                          {
                            "facilityId": 784,
                            "name": "Housekeeping on request"
                          },
                          {
                            "facilityId": 857,
                            "name": "Covered parking"
                          },
                          {
                            "facilityId": 859,
                            "name": "Secured parking"
                          },
                          {
                            "facilityId": 880,
                            "name": "Garage"
                          }
                        ],
                        "rooms": [
                          {
                            "id": 2617264,
                            "roomName": "Standard Room",
                            "description": "The double room includes a private bathroom equipped with a hairdryer. The air-conditioned double room provides a TV with satellite channels, soundproof walls, a safe deposit box, heating as well as city views.",
                            "roomSizeSquare": 23,
                            "roomSizeUnit": "m2",
                            "hotelId": "436827",
                            "maxAdults": 2,
                            "maxChildren": 1,
                            "maxOccupancy": 2,
                            "ranking": 1,
                            "bedTypes": [],
                            "roomAmenities": [
                              {
                                "amenitiesId": 293,
                                "name": "Safety deposit box",
                                "sort": 17
                              },
                              {
                                "amenitiesId": 339,
                                "name": "TV",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 342,
                                "name": "Telephone",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 11,
                                "name": "Air conditioning",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 148,
                                "name": "Hair dryer",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 350,
                                "name": "Toilet",
                                "sort": 10
                              },
                              {
                                "amenitiesId": 266,
                                "name": "Private bathroom",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 156,
                                "name": "Heating",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 295,
                                "name": "Satellite channels",
                                "sort": 20
                              },
                              {
                                "amenitiesId": 176,
                                "name": "Interconnected room(s) available",
                                "sort": 63
                              },
                              {
                                "amenitiesId": 371,
                                "name": "View",
                                "sort": 31
                              },
                              {
                                "amenitiesId": 57,
                                "name": "City view",
                                "sort": 39
                              },
                              {
                                "amenitiesId": 197,
                                "name": "Linen",
                                "sort": 43
                              },
                              {
                                "amenitiesId": 366,
                                "name": "Upper floors accessible by elevator",
                                "sort": 166
                              },
                              {
                                "amenitiesId": 352,
                                "name": "Toilet paper",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 361,
                                "name": "Trash cans",
                                "sort": 85
                              },
                              {
                                "amenitiesId": 327,
                                "name": "Socket near the bed",
                                "sort": 89
                              },
                              {
                                "amenitiesId": 5,
                                "name": "Accessible by lift",
                                "sort": 29
                              },
                              {
                                "amenitiesId": 322,
                                "name": "Smoke alarm",
                                "sort": 182
                              },
                              {
                                "amenitiesId": 182,
                                "name": "Key card access",
                                "sort": 158
                              }
                            ],
                            "photos": [
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/592392806.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/592392806.jpg?k=a4eb5eb9a6a889c0de416084b723b6697b3c5d2c84bd81413c95734b671aa6e1&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/592392806.jpg"
                              }
                            ],
                            "bedRelation": "NONE"
                          },
                          {
                            "id": 2617265,
                            "roomName": "Premium Room",
                            "description": "The double room includes a private bathroom fitted with a hairdryer. The spacious air-conditioned double room offers a TV with satellite channels, soundproof walls, a safe deposit box, heating as well as city views.",
                            "roomSizeSquare": 26,
                            "roomSizeUnit": "m2",
                            "hotelId": "436827",
                            "maxAdults": 2,
                            "maxChildren": 1,
                            "maxOccupancy": 2,
                            "ranking": 1,
                            "bedTypes": [],
                            "roomAmenities": [
                              {
                                "amenitiesId": 293,
                                "name": "Safety deposit box",
                                "sort": 17
                              },
                              {
                                "amenitiesId": 339,
                                "name": "TV",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 342,
                                "name": "Telephone",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 11,
                                "name": "Air conditioning",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 148,
                                "name": "Hair dryer",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 350,
                                "name": "Toilet",
                                "sort": 10
                              },
                              {
                                "amenitiesId": 266,
                                "name": "Private bathroom",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 156,
                                "name": "Heating",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 295,
                                "name": "Satellite channels",
                                "sort": 20
                              },
                              {
                                "amenitiesId": 176,
                                "name": "Interconnected room(s) available",
                                "sort": 63
                              },
                              {
                                "amenitiesId": 371,
                                "name": "View",
                                "sort": 31
                              },
                              {
                                "amenitiesId": 57,
                                "name": "City view",
                                "sort": 39
                              },
                              {
                                "amenitiesId": 197,
                                "name": "Linen",
                                "sort": 43
                              },
                              {
                                "amenitiesId": 366,
                                "name": "Upper floors accessible by elevator",
                                "sort": 166
                              },
                              {
                                "amenitiesId": 352,
                                "name": "Toilet paper",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 361,
                                "name": "Trash cans",
                                "sort": 85
                              },
                              {
                                "amenitiesId": 327,
                                "name": "Socket near the bed",
                                "sort": 89
                              },
                              {
                                "amenitiesId": 5,
                                "name": "Accessible by lift",
                                "sort": 29
                              },
                              {
                                "amenitiesId": 322,
                                "name": "Smoke alarm",
                                "sort": 182
                              },
                              {
                                "amenitiesId": 182,
                                "name": "Key card access",
                                "sort": 158
                              }
                            ],
                            "photos": [
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/527434935.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/527434935.jpg?k=c0d05d76c837a33c0df60899e25feb97cf8fecf8b89244422e63bdef2203157b&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/527434935.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489119.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489119.jpg?k=0488a1da0924228e67bbd3df87c868a9d7d5019bb42cdfaf7a395dc2d4cab9df&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489119.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489114.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489114.jpg?k=d2855ccba7916212023cfa373066f32eafb4d6b7ea5e98b3e74440fccd53df24&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489114.jpg"
                              }
                            ],
                            "bedRelation": "NONE"
                          },
                          {
                            "id": 2617266,
                            "roomName": "Standard Queen Room",
                            "description": "The double room includes a private bathroom fitted with a hairdryer. The air-conditioned double room offers a TV with satellite channels, soundproof walls, a safe deposit box, heating as well as city views. The unit has 1 bed.",
                            "roomSizeSquare": 23,
                            "roomSizeUnit": "m2",
                            "hotelId": "436827",
                            "maxAdults": 2,
                            "maxChildren": 1,
                            "maxOccupancy": 2,
                            "ranking": 1,
                            "bedTypes": [
                              {
                                "quantity": 1,
                                "bedType": "Large bed (King size)",
                                "bedSize": "151-180cm wide"
                              }
                            ],
                            "roomAmenities": [
                              {
                                "amenitiesId": 293,
                                "name": "Safety deposit box",
                                "sort": 17
                              },
                              {
                                "amenitiesId": 339,
                                "name": "TV",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 342,
                                "name": "Telephone",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 11,
                                "name": "Air conditioning",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 148,
                                "name": "Hair dryer",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 350,
                                "name": "Toilet",
                                "sort": 10
                              },
                              {
                                "amenitiesId": 266,
                                "name": "Private bathroom",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 156,
                                "name": "Heating",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 295,
                                "name": "Satellite channels",
                                "sort": 20
                              },
                              {
                                "amenitiesId": 371,
                                "name": "View",
                                "sort": 31
                              },
                              {
                                "amenitiesId": 57,
                                "name": "City view",
                                "sort": 39
                              },
                              {
                                "amenitiesId": 197,
                                "name": "Linen",
                                "sort": 43
                              },
                              {
                                "amenitiesId": 366,
                                "name": "Upper floors accessible by elevator",
                                "sort": 166
                              },
                              {
                                "amenitiesId": 352,
                                "name": "Toilet paper",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 361,
                                "name": "Trash cans",
                                "sort": 85
                              },
                              {
                                "amenitiesId": 327,
                                "name": "Socket near the bed",
                                "sort": 89
                              },
                              {
                                "amenitiesId": 5,
                                "name": "Accessible by lift",
                                "sort": 29
                              },
                              {
                                "amenitiesId": 322,
                                "name": "Smoke alarm",
                                "sort": 182
                              },
                              {
                                "amenitiesId": 182,
                                "name": "Key card access",
                                "sort": 158
                              }
                            ],
                            "photos": [
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/528870978.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/528870978.jpg?k=ebb8f0bb34ad36718ced190d11fddd683a61100ac95a3b5fdf1f5c0194a5194d&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/528870978.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489106.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489106.jpg?k=438c14c18623b4c6ce67ba8b3b1343ce591f873ffd5940e34d1255a26a88ef4d&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489106.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489112.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489112.jpg?k=5248c6024a1447a7dca20dccf2933f81618f401dc809b0a4e3427403760ecab8&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489112.jpg"
                              }
                            ],
                            "bedRelation": "NONE"
                          },
                          {
                            "id": 2617267,
                            "roomName": "Standard King Room",
                            "description": "The double room includes a private bathroom fitted with a hairdryer. The spacious air-conditioned double room features a TV with satellite channels, soundproof walls, a safe deposit box, heating as well as city views. The unit has 1 bed.",
                            "roomSizeSquare": 26,
                            "roomSizeUnit": "m2",
                            "hotelId": "436827",
                            "maxAdults": 2,
                            "maxChildren": 1,
                            "maxOccupancy": 2,
                            "ranking": 1,
                            "bedTypes": [
                              {
                                "quantity": 1,
                                "bedType": "Extra-large double bed (Super-king size)",
                                "bedSize": "181-210 cm wide"
                              }
                            ],
                            "roomAmenities": [
                              {
                                "amenitiesId": 293,
                                "name": "Safety deposit box",
                                "sort": 17
                              },
                              {
                                "amenitiesId": 339,
                                "name": "TV",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 342,
                                "name": "Telephone",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 11,
                                "name": "Air conditioning",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 148,
                                "name": "Hair dryer",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 350,
                                "name": "Toilet",
                                "sort": 10
                              },
                              {
                                "amenitiesId": 266,
                                "name": "Private bathroom",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 156,
                                "name": "Heating",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 295,
                                "name": "Satellite channels",
                                "sort": 20
                              },
                              {
                                "amenitiesId": 176,
                                "name": "Interconnected room(s) available",
                                "sort": 63
                              },
                              {
                                "amenitiesId": 371,
                                "name": "View",
                                "sort": 31
                              },
                              {
                                "amenitiesId": 57,
                                "name": "City view",
                                "sort": 39
                              },
                              {
                                "amenitiesId": 197,
                                "name": "Linen",
                                "sort": 43
                              },
                              {
                                "amenitiesId": 366,
                                "name": "Upper floors accessible by elevator",
                                "sort": 166
                              },
                              {
                                "amenitiesId": 352,
                                "name": "Toilet paper",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 361,
                                "name": "Trash cans",
                                "sort": 85
                              },
                              {
                                "amenitiesId": 327,
                                "name": "Socket near the bed",
                                "sort": 89
                              },
                              {
                                "amenitiesId": 5,
                                "name": "Accessible by lift",
                                "sort": 29
                              },
                              {
                                "amenitiesId": 322,
                                "name": "Smoke alarm",
                                "sort": 182
                              },
                              {
                                "amenitiesId": 182,
                                "name": "Key card access",
                                "sort": 158
                              }
                            ],
                            "photos": [
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/528870820.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/528870820.jpg?k=34baac1cd0442eaa0a3b12df5b678ab61f22c64d8c396dc8e470a8656b921a6e&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/528870820.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/528870858.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/528870858.jpg?k=8a70f991486b34ba08fa88c9d18583b6b35d83f9bef71129157a8b625fa683af&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/528870858.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489106.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489106.jpg?k=438c14c18623b4c6ce67ba8b3b1343ce591f873ffd5940e34d1255a26a88ef4d&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489106.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489133.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489133.jpg?k=8a31e539abdd92ca9b544a7b1b162c85483dd28611cd8f62f8a5d7411dad32a8&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489133.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489112.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489112.jpg?k=5248c6024a1447a7dca20dccf2933f81618f401dc809b0a4e3427403760ecab8&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489112.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489116.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489116.jpg?k=03e55f2f056007a3c9d123d1590a7349b4fffd8fe95a6b34c1bb12502ebd928f&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489116.jpg"
                              }
                            ],
                            "bedRelation": "NONE"
                          },
                          {
                            "id": 2617268,
                            "roomName": "Standard Double Room with Two Double Beds",
                            "description": "The double room includes a private bathroom equipped with a hairdryer. The spacious air-conditioned double room offers a TV with satellite channels, soundproof walls, a safe deposit box, heating as well as city views. The unit offers 2 beds.",
                            "roomSizeSquare": 27,
                            "roomSizeUnit": "m2",
                            "hotelId": "436827",
                            "maxAdults": 4,
                            "maxChildren": 3,
                            "maxOccupancy": 4,
                            "ranking": 5,
                            "bedTypes": [
                              {
                                "quantity": 2,
                                "bedType": "Double bed",
                                "bedSize": "131-150 cm wide"
                              }
                            ],
                            "roomAmenities": [
                              {
                                "amenitiesId": 293,
                                "name": "Safety deposit box",
                                "sort": 17
                              },
                              {
                                "amenitiesId": 339,
                                "name": "TV",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 342,
                                "name": "Telephone",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 11,
                                "name": "Air conditioning",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 148,
                                "name": "Hair dryer",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 350,
                                "name": "Toilet",
                                "sort": 10
                              },
                              {
                                "amenitiesId": 266,
                                "name": "Private bathroom",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 156,
                                "name": "Heating",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 295,
                                "name": "Satellite channels",
                                "sort": 20
                              },
                              {
                                "amenitiesId": 176,
                                "name": "Interconnected room(s) available",
                                "sort": 63
                              },
                              {
                                "amenitiesId": 371,
                                "name": "View",
                                "sort": 31
                              },
                              {
                                "amenitiesId": 57,
                                "name": "City view",
                                "sort": 39
                              },
                              {
                                "amenitiesId": 197,
                                "name": "Linen",
                                "sort": 43
                              },
                              {
                                "amenitiesId": 366,
                                "name": "Upper floors accessible by elevator",
                                "sort": 166
                              },
                              {
                                "amenitiesId": 352,
                                "name": "Toilet paper",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 361,
                                "name": "Trash cans",
                                "sort": 85
                              },
                              {
                                "amenitiesId": 327,
                                "name": "Socket near the bed",
                                "sort": 89
                              },
                              {
                                "amenitiesId": 5,
                                "name": "Accessible by lift",
                                "sort": 29
                              },
                              {
                                "amenitiesId": 322,
                                "name": "Smoke alarm",
                                "sort": 182
                              },
                              {
                                "amenitiesId": 182,
                                "name": "Key card access",
                                "sort": 158
                              }
                            ],
                            "photos": [
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489112.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489112.jpg?k=5248c6024a1447a7dca20dccf2933f81618f401dc809b0a4e3427403760ecab8&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489112.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489056.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489056.jpg?k=6e138c72f652812bde87f44ebf164ec3a2085ed10650caa87e0a4ce4e8ee9b0b&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489056.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489039.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489039.jpg?k=8670f0f6b3e0888d7714dd4da3ba9a7d422e21b2580d066593e2c281161ea5dd&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489039.jpg"
                              }
                            ],
                            "bedRelation": "NONE"
                          },
                          {
                            "id": 2617269,
                            "roomName": "Premium Queen Room - Manhattan Floor",
                            "description": "The double room features air conditioning, soundproof walls, as well as a private bathroom boasting a walk-in shower and a hairdryer. The unit has 1 bed.",
                            "roomSizeSquare": 23,
                            "roomSizeUnit": "m2",
                            "hotelId": "436827",
                            "maxAdults": 2,
                            "maxChildren": 1,
                            "maxOccupancy": 2,
                            "ranking": 1,
                            "bedTypes": [
                              {
                                "quantity": 1,
                                "bedType": "Large bed (King size)",
                                "bedSize": "151-180cm wide"
                              }
                            ],
                            "roomAmenities": [
                              {
                                "amenitiesId": 293,
                                "name": "Safety deposit box",
                                "sort": 17
                              },
                              {
                                "amenitiesId": 339,
                                "name": "TV",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 342,
                                "name": "Telephone",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 11,
                                "name": "Air conditioning",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 148,
                                "name": "Hair dryer",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 350,
                                "name": "Toilet",
                                "sort": 10
                              },
                              {
                                "amenitiesId": 266,
                                "name": "Private bathroom",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 156,
                                "name": "Heating",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 295,
                                "name": "Satellite channels",
                                "sort": 20
                              },
                              {
                                "amenitiesId": 371,
                                "name": "View",
                                "sort": 31
                              },
                              {
                                "amenitiesId": 57,
                                "name": "City view",
                                "sort": 39
                              },
                              {
                                "amenitiesId": 197,
                                "name": "Linen",
                                "sort": 43
                              },
                              {
                                "amenitiesId": 366,
                                "name": "Upper floors accessible by elevator",
                                "sort": 166
                              },
                              {
                                "amenitiesId": 352,
                                "name": "Toilet paper",
                                "sort": 0
                              },
                              {
                                "amenitiesId": 361,
                                "name": "Trash cans",
                                "sort": 85
                              },
                              {
                                "amenitiesId": 327,
                                "name": "Socket near the bed",
                                "sort": 89
                              },
                              {
                                "amenitiesId": 5,
                                "name": "Accessible by lift",
                                "sort": 29
                              },
                              {
                                "amenitiesId": 322,
                                "name": "Smoke alarm",
                                "sort": 182
                              },
                              {
                                "amenitiesId": 182,
                                "name": "Key card access",
                                "sort": 158
                              }
                            ],
                            "photos": [
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/527434935.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/527434935.jpg?k=c0d05d76c837a33c0df60899e25feb97cf8fecf8b89244422e63bdef2203157b&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/527434935.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489119.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489119.jpg?k=0488a1da0924228e67bbd3df87c868a9d7d5019bb42cdfaf7a395dc2d4cab9df&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489119.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489094.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489094.jpg?k=d06c959703dd03ad7dd5515a91601fcdb3e7ac0d66e494a46fa1ab566c01bd10&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489094.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489074.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489074.jpg?k=a95c4b2c1749966628b58726c5f75e6c1c0dc3147814f523cf2482a73cfcf063&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489074.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/524489112.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/524489112.jpg?k=5248c6024a1447a7dca20dccf2933f81618f401dc809b0a4e3427403760ecab8&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/524489112.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/523933085.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/523933085.jpg?k=b3dcaeea6fc047da6cc2e4ef9a1c43b17e71129e16662dc217b24384fc028cfb&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/523933085.jpg"
                              },
                              {
                                "url": "https://static.cupid.travel/rooms-large-pictures/618690410.jpg",
                                "imageDescription": "",
                                "imageClass1": "",
                                "imageClass2": "",
                                "failoverPhoto": "https://q-xx.bstatic.com/xdata/images/hotel/max1200/618690410.jpg?k=9792a5c999a98b01daed4502de27f2edaecf8cc3b705ca6e0d4adfcc1ad62d4c&o=",
                                "mainPhoto": false,
                                "score": 0,
                                "classId": 0,
                                "classOrder": 0,
                                "hd_url": "https://static.cupid.travel/rooms-large-pictures/618690410.jpg"
                              }
                            ],
                            "bedRelation": "NONE"
                          }
                        ],
                        "phone": "",
                        "fax": "",
                        "email": "",
                        "hotelType": "Hotels",
                        "hotelTypeId": 204,
                        "airportCode": "LGA",
                        "rating": 5.6,
                        "reviewCount": 6536,
                        "rohId": 20712,
                        "parking": "",
                        "groupRoomMin": 0,
                        "childAllowed": false,
                        "petsAllowed": false,
                        "policies": [
                          {
                            "policy_type": "POLICY_HOTEL_GROUPS",
                            "name": "Groups",
                            "description": "When booking more than 5 rooms, different policies and additional supplements may apply.\n",
                            "child_allowed": "",
                            "pets_allowed": "",
                            "parking": ""
                          },
                          {
                            "policy_type": "POLICY_HOTEL_INTERNET",
                            "name": "Internet",
                            "description": "WiFi is available in public areas and is free of charge.\n",
                            "child_allowed": "",
                            "pets_allowed": "",
                            "parking": ""
                          },
                          {
                            "policy_type": "POLICY_HOTEL_PARKING",
                            "name": "Parking",
                            "description": "Public parking is possible on site (reservation is not needed) and costs  USD 72 per  day.\n",
                            "child_allowed": "",
                            "pets_allowed": "",
                            "parking": ""
                          },
                          {
                            "policy_type": "POLICY_HOTEL_PETS",
                            "name": "Pets",
                            "description": "Pets are allowed. Charges may be applicable.\n",
                            "child_allowed": "",
                            "pets_allowed": "",
                            "parking": ""
                          },
                          {
                            "policy_type": "POLICY_CHILDREN",
                            "name": "Children and extra bed policy",
                            "description": "Children of any age are allowed.\nChildren up to and including 17 years old stay for free when using an existing bed.\nYou haven't added any cots.\nYou haven't added any extra beds.",
                            "child_allowed": "",
                            "pets_allowed": "",
                            "parking": ""
                          }
                        ],
                        "sentiment_analysis": {
                          "cons": [
                            "Limited parking",
                            "Expensive breakfast",
                            "No spa services",
                            "Dated rooms",
                            "Uncomfortable beds",
                            "Rude staff",
                            "Noisy renovations",
                            "Lack of basic amenities",
                            "Overpriced rooms",
                            "Limited food options",
                            "Unsatisfactory value for money",
                            "Heating issues",
                            "Stained towels and bathrooms",
                            "Delayed deposit refund"
                          ],
                          "pros": [
                            "Location",
                            "Friendly staff",
                            "Near Times Square",
                            "Clean rooms",
                            "View",
                            "Bed cleanliness"
                          ],
                          "categories": [
                            {
                              "name": "Cleanliness",
                              "rating": 5.9,
                              "description": "Mixed reviews on cleanliness with some guests mentioning dirty rugs, uncomfortable beds, and rude staff, while others appreciated the cleanliness of the rooms."
                            },
                            {
                              "name": "Service",
                              "rating": 7.6,
                              "description": "Service was a point of contention, with some guests praising the friendly staff and the efforts to accommodate guests, but others complained about rude staff and long wait times for elevators."
                            },
                            {
                              "name": "Location",
                              "rating": 8.3,
                              "description": "The hotel's location was a standout feature, with many guests appreciating its proximity to key landmarks and attractions."
                            },
                            {
                              "name": "Room Quality",
                              "rating": 4.9,
                              "description": "Rooms received mixed reviews, with complaints about outdated rooms, uncomfortable beds, and noisy renovations, alongside positive comments about clean bedrooms and comfortable beds."
                            },
                            {
                              "name": "Amenities",
                              "rating": 6.7,
                              "description": "Guests were divided on amenities, with some highlighting the lack of basic facilities like restaurant and lounges, while others appreciated the clean bedrooms and gym facilities."
                            },
                            {
                              "name": "Value for Money",
                              "rating": 5.5,
                              "description": "Opinions on value for money were split, with some guests feeling the hotel was overpriced for the services offered, while others found it reasonable given the location."
                            },
                            {
                              "name": "Food and Beverage",
                              "rating": 6.9,
                              "description": "Feedback on food and beverage services was mixed, with some guests criticizing the limited breakfast options, while others found it satisfactory."
                            },
                            {
                              "name": "Overall Experience",
                              "rating": 6.3,
                              "description": "Most guests had a mixed experience, with some enjoying their stay and recommending the hotel, while others reported disappointing experiences."
                            }
                          ]
                        },
                        "sentiment_updated_at": "2026-07-08T14:33:49Z"
                      }
                    }
                  }
                }
              }
            }
          },
          "400": {
            "description": "Bad Request",
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
                          "description": "Detailed message providing context about the error"
                        }
                      }
                    }
                  }
                },
                "examples": {
                  "hotelId is missing or invalid": {
                    "value": {
                      "error": {
                        "code": 4002,
                        "description": "hotelId is missing or invalid",
                        "message": "required request field is missing or wrong input"
                      }
                    }
                  }
                }
              }
            }
          },
          "401": {
            "description": "Unauthorized",
            "headers": {
              "Content-Type": {
                "schema": {
                  "type": "string",
                  "example": "application/json; charset=utf-8"
                }
              },
              "Content-Length": {
                "schema": {
                  "type": "string",
                  "example": "47"
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
                          "description": "Detailed message providing context about the error"
                        }
                      }
                    }
                  }
                },
                "examples": {
                  "Unauthorized": {
                    "value": {
                      "error": {
                        "code": 401,
                        "message": "Unauthorized"
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