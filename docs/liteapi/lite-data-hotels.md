---
updatedAt: 2026-06-02T09:22:54.000Z
agentTools:
  projectIndex: https://docs.liteapi.travel/llms.txt
---

# Retrieve a list of hotels

## Overview

Search and retrieve hotel listings based on various criteria. Get hotel metadata including names, addresses, ratings, amenities, and images for display in your application.

## When to Use

- **Hotel listings** - Display hotel search results
- **Location-based search** - Find hotels by city, coordinates, or Place ID
- **Hotel discovery** - Browse hotels in specific areas
- **Metadata retrieval** - Get hotel information for display

## What You Get

- **Hotel list** - Matching hotels with complete metadata
- **Basic information** - Names, addresses, ratings, and locations
- **Amenities** - Available facilities and features
- **Images** - Hotel photos for display
- **Identifiers** - Hotel IDs for use in rate searches

## Search Options

- **By city** - Search hotels in a specific city
- **By coordinates** - Find hotels near latitude/longitude with radius
- **By Place ID** - Get hotels within a specific place boundary
- **By hotel IDs** - Retrieve specific hotels by their IDs

## Quick Start

Provide search criteria (city, coordinates+radius, placeId, or hotelIds). Returns matching hotels with complete metadata.

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
    "/data/hotels": {
      "get": {
        "tags": [
          "Hotels"
        ],
        "summary": "Retrieve a list of hotels",
        "description": "## Overview\n\nSearch and retrieve hotel listings based on various criteria. Get hotel metadata including names, addresses, ratings, amenities, and images for display in your application.\n\n## When to Use\n\n- **Hotel listings** - Display hotel search results\n- **Location-based search** - Find hotels by city, coordinates, or Place ID\n- **Hotel discovery** - Browse hotels in specific areas\n- **Metadata retrieval** - Get hotel information for display\n\n## What You Get\n\n- **Hotel list** - Matching hotels with complete metadata\n- **Basic information** - Names, addresses, ratings, and locations\n- **Amenities** - Available facilities and features\n- **Images** - Hotel photos for display\n- **Identifiers** - Hotel IDs for use in rate searches\n\n## Search Options\n\n- **By city** - Search hotels in a specific city\n- **By coordinates** - Find hotels near latitude/longitude with radius\n- **By Place ID** - Get hotels within a specific place boundary\n- **By hotel IDs** - Retrieve specific hotels by their IDs\n\n## Quick Start\n\nProvide search criteria (city, coordinates+radius, placeId, or hotelIds). Returns matching hotels with complete metadata.",
        "security": [
          {
            "apikeyAuth": []
          }
        ],
        "parameters": [
          {
            "name": "countryCode",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Country code ISO-2 code - example (SG)",
            "example": "US"
          },
          {
            "name": "cityName",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Name of the city",
            "example": "New York"
          },
          {
            "name": "hotelName",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Name of the hotel (loose match, case-insensitive, e.g. 'hilton')",
            "example": "Hilton"
          },
          {
            "name": "offset",
            "in": "query",
            "schema": {
              "type": "integer"
            },
            "description": "Specifies the number of rows to skip before starting to return rows",
            "example": "0"
          },
          {
            "name": "limit",
            "in": "query",
            "schema": {
              "type": "integer"
            },
            "description": "Specifies the maximum number of results to return. By default, this is set to 200, even if not explicitly defined. If a higher limit is specified, the maximum allowed is 5000 results",
            "example": "1000"
          },
          {
            "name": "lastUpdatedAt",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Retrieve only the hotels that have been updated since the provided date and time (using the RFC3339 format)",
            "example": "2026-07-05T15:00:00Z"
          },
          {
            "name": "longitude",
            "in": "query",
            "schema": {
              "type": "number"
            },
            "description": "Longitude geo coordinates",
            "example": "-115.16988"
          },
          {
            "name": "latitude",
            "in": "query",
            "schema": {
              "type": "number"
            },
            "description": "Latitude geo coordinates",
            "example": "36.12510"
          },
          {
            "name": "radius",
            "in": "query",
            "schema": {
              "type": "integer"
            },
            "description": "radius in meters (min 1000m)",
            "example": "10000"
          },
          {
            "name": "aiSearch",
            "in": "query",
            "schema": {
              "type": "string"
            },
            "description": "Search term for AI search. Uses semantic search to find hotels matching the query intent. Examples: 'Romantic getaway with Italian vibes in London near the London Eye', 'Hotels near the Eiffel tower'",
            "example": "Romantic getaway with Italian vibes in London near the London Eye"
          },
          {
            "schema": {
              "type": "number",
              "format": "float"
            },
            "in": "query",
            "name": "timeout",
            "description": "request timeout in seconds",
            "example": "1.5"
          },
          {
            "name": "zip",
            "schema": {
              "type": "string"
            },
            "in": "query",
            "description": "ZIP code of the location",
            "example": "08401"
          },
          {
            "name": "minRating",
            "schema": {
              "type": "number",
              "format": "float"
            },
            "in": "query",
            "description": "Minimum rating of the hotel. e.g. 8.6",
            "example": "8.6"
          },
          {
            "name": "minReviewsCount",
            "schema": {
              "type": "number",
              "format": "integer"
            },
            "in": "query",
            "description": "Minimum number of reviews. e.g. 100",
            "example": "100"
          },
          {
            "name": "facilityIds",
            "schema": {
              "type": "string"
            },
            "in": "query",
            "description": "Comma-separated list of facilities. e.g. '1,2,3'",
            "example": "1,2,3"
          },
          {
            "name": "hotelTypeIds",
            "schema": {
              "type": "string"
            },
            "in": "query",
            "description": "Comma-separated list of hotel types. e.g. '201,204,208'",
            "example": "201,204,208"
          },
          {
            "name": "chainIds",
            "schema": {
              "type": "string"
            },
            "in": "query",
            "description": "Comma-separated list of hotel chain ids. e.g. '14675,14677'",
            "example": "14675,14677"
          },
          {
            "name": "strictFacilitiesFiltering",
            "schema": {
              "type": "boolean"
            },
            "in": "query",
            "description": "If `true`, only hotels with all the specified facilities will be returned",
            "example": false
          },
          {
            "name": "starRating",
            "schema": {
              "type": "string"
            },
            "in": "query",
            "description": "Comma-separated list of star ratings. Note: star ratings have 2 allowed decimals '.0' and '.5' from 1 to 5. e.g. '3.5,4.0,5.0'",
            "example": "3.5,4.0,5.0"
          },
          {
            "name": "placeId",
            "schema": {
              "type": "string"
            },
            "in": "query",
            "description": "Unique ID of a place retrieved from the `/data/places` endpoint. When provided, the API fetches place details and searches for hotels within a 1km radius of the place location center, or a specific hotel when the placeId refers to a property. The response includes a `place` object containing the place information used in the search.",
            "example": "ChIJLc8UZFdqXz4ROODwebhx2rM"
          },
          {
            "name": "language",
            "schema": {
              "type": "string"
            },
            "in": "query",
            "description": "The language code, indicating in which language the results should be returned. e.g. 'fr'",
            "example": "fr"
          },
          {
            "name": "hotelIds",
            "schema": {
              "type": "string"
            },
            "in": "query",
            "description": "Comma-separated list of hotel IDs (e.g., 'lp1897,lp1343') to fetch specific hotels by their IDs. This is a valid main query parameter that can be used instead of other search criteria.",
            "example": "lp1897,lp1343"
          },
          {
            "name": "advancedAccessibilityOnly",
            "schema": {
              "type": "boolean"
            },
            "in": "query",
            "description": "If `true`, only hotels with advanced accessibility will be returned",
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
                      "type": "array",
                      "items": {
                        "type": "object",
                        "required": [
                          "id",
                          "name",
                          "country",
                          "city",
                          "latitude",
                          "longitude"
                        ],
                        "properties": {
                          "id": {
                            "type": "string",
                            "description": "Unique identifier of the hotel"
                          },
                          "name": {
                            "type": "string",
                            "description": "Name of the hotel"
                          },
                          "hotelDescription": {
                            "type": "string",
                            "description": "A brief description of the hotel and its facilities"
                          },
                          "currency": {
                            "type": "string",
                            "description": "The currency code used for hotel pricing (e.g., USD, EUR)"
                          },
                          "country": {
                            "type": "string",
                            "description": "The country where the hotel is located"
                          },
                          "city": {
                            "type": "string",
                            "description": "The city where the hotel is situated"
                          },
                          "latitude": {
                            "type": "number",
                            "description": "The latitude coordinate of the hotel's location"
                          },
                          "longitude": {
                            "type": "number",
                            "description": "The longitude coordinate of the hotel's location"
                          },
                          "address": {
                            "type": "string",
                            "description": "The street address of the hotel"
                          },
                          "zip": {
                            "type": "string",
                            "description": "The postal code of the hotel"
                          },
                          "main_photo": {
                            "type": "string",
                            "description": "The main image representing the hotel"
                          },
                          "thumbnail": {
                            "type": "string",
                            "description": "A smaller version of the main photo used for previews"
                          },
                          "stars": {
                            "type": "number",
                            "description": "The hotel's star rating (e.g., 3, 4, 5 stars)"
                          },
                          "hotelTypeId": {
                            "type": "number",
                            "description": "The unique identifier for the hotel's category or type"
                          },
                          "chainId": {
                            "type": "number",
                            "description": "The unique identifier of the hotel chain"
                          },
                          "chain": {
                            "type": "string",
                            "description": "The name of the hotel chain, if applicable (e.g., Hilton, Marriott)."
                          },
                          "accessibilityAttributes": {
                            "type": "object",
                            "properties": {
                              "attributes": {
                                "type": "array",
                                "items": {
                                  "type": "string"
                                },
                                "description": "List of attributes representing boolean fields that are true."
                              },
                              "showerChair": {
                                "type": "string",
                                "nullable": true,
                                "description": "Information about the availability of a shower chair."
                              },
                              "entranceType": {
                                "type": "string",
                                "nullable": true,
                                "description": "Type of entrance, e.g., automatic or manual."
                              },
                              "petFriendly": {
                                "type": "string",
                                "description": "Indicates if the hotel is pet-friendly."
                              },
                              "rampAngle": {
                                "type": "number",
                                "format": "float",
                                "description": "The angle of the ramp in degrees."
                              },
                              "rampLength": {
                                "type": "number",
                                "format": "float",
                                "description": "The length of the ramp in meters."
                              },
                              "entranceDoorWidth": {
                                "type": "integer",
                                "description": "Width of the entrance door in centimeters."
                              },
                              "roomMaxGuestsNumber": {
                                "type": "integer",
                                "description": "Maximum number of guests allowed in the room."
                              },
                              "distanceFromTheElevatorToTheAccessibleRoom": {
                                "type": "number",
                                "format": "float",
                                "description": "Distance from the elevator to the accessible room in meters."
                              }
                            },
                            "required": [
                              "attributes",
                              "petFriendly",
                              "rampAngle",
                              "rampLength",
                              "entranceDoorWidth",
                              "roomMaxGuestsNumber",
                              "distanceFromTheElevatorToTheAccessibleRoom"
                            ],
                            "description": "A compact representation of the hotel's accessibility features."
                          },
                          "rohId": {
                            "type": "integer",
                            "description": "Room of House ID - unique identifier for the hotel property."
                          },
                          "deletedAt": {
                            "type": "string",
                            "description": "Timestamp indicating when hotel is deleted in our system"
                          },
                          "score": {
                            "type": "number",
                            "format": "float",
                            "description": "Similarity score (0.0-1.0) indicating how well the hotel name matches the search query when searching by placeId. Higher scores indicate better matches. Only present when placeId parameter is used."
                          },
                          "rating": {
                            "type": "number",
                            "format": "float",
                            "description": "The average guest rating for the hotel (e.g., 8.5 out of 10)"
                          },
                          "reviewCount": {
                            "type": "integer",
                            "description": "The total number of guest reviews for the hotel"
                          },
                          "facilityIds": {
                            "type": "array",
                            "items": {
                              "type": "integer"
                            },
                            "description": "Array of facility IDs available at the hotel (e.g., [1, 2, 3] representing WiFi, Pool, Gym)"
                          }
                        }
                      }
                    },
                    "hotelIds": {
                      "type": "array",
                      "description": "An array of unique hotel identifiers that match the specified filters or query parameters, allowing quick reference to specific hotels.",
                      "items": {
                        "type": "string"
                      }
                    },
                    "total": {
                      "type": "integer",
                      "description": "The total number of hotels retrieved based on the applied filters or query parameters."
                    },
                    "place": {
                      "type": "object",
                      "description": "Place information used in the search. Only present when placeId parameter is used.",
                      "properties": {
                        "placeId": {
                          "type": "string",
                          "description": "The place ID used in the search"
                        },
                        "displayName": {
                          "type": "string",
                          "description": "The display name of the place (e.g., 'Marriott New York JFK Airport')"
                        },
                        "addressComponents": {
                          "type": "array",
                          "description": "A list of address components, each representing a part of the address such as country, city, or postal code.",
                          "items": {
                            "type": "object",
                            "properties": {
                              "languageCode": {
                                "type": "string",
                                "description": "The language code for the address component (e.g., 'en', 'en-US')."
                              },
                              "longText": {
                                "type": "string",
                                "description": "The full text of the address component (e.g., 'France')."
                              },
                              "shortText": {
                                "type": "string",
                                "description": "The abbreviated or short text for the address component (e.g., 'FR')."
                              },
                              "types": {
                                "type": "array",
                                "description": "A list of types describing the address component (e.g., 'country', 'political').",
                                "items": {
                                  "type": "string"
                                }
                              }
                            }
                          }
                        },
                        "location": {
                          "type": "object",
                          "description": "The geographic coordinates of the place.",
                          "properties": {
                            "latitude": {
                              "type": "number",
                              "description": "The latitude of the location."
                            },
                            "longitude": {
                              "type": "number",
                              "description": "The longitude of the location."
                            }
                          }
                        },
                        "viewport": {
                          "type": "object",
                          "description": "The recommended viewport for displaying the place, defined by high and low latitude/longitude bounds.",
                          "properties": {
                            "high": {
                              "type": "object",
                              "description": "The northeast corner of the viewport.",
                              "properties": {
                                "latitude": {
                                  "type": "number",
                                  "description": "The latitude of the high (northeast) corner."
                                },
                                "longitude": {
                                  "type": "number",
                                  "description": "The longitude of the high (northeast) corner."
                                }
                              }
                            },
                            "low": {
                              "type": "object",
                              "description": "The southwest corner of the viewport.",
                              "properties": {
                                "latitude": {
                                  "type": "number",
                                  "description": "The latitude of the low (southwest) corner."
                                },
                                "longitude": {
                                  "type": "number",
                                  "description": "The longitude of the low (southwest) corner."
                                }
                              }
                            }
                          }
                        },
                        "types": {
                          "type": "array",
                          "description": "A list of place types (e.g., 'lodging', 'hotel', 'establishment').",
                          "items": {
                            "type": "string"
                          }
                        }
                      }
                    }
                  }
                },
                "examples": {
                  "HR Sibenik 3 hotels": {
                    "value": {
                      "data": [
                        {
                          "id": "lp42fec",
                          "name": "Hotel Jadran",
                          "hotelDescription": "Location : With a stay at Hotel Jadran in Sibenik (Sibenik Old Town), you'll be steps from Museum of Church Art and Church of St. Barbara.  This hotel is 10.6 mi (17 km) from Krka National Park and 0.1 mi (0.1 km) from Sibenik Aquarium.<br/>Amenities : Take in the views from a terrace and a garden and make use of amenities such as complimentary wireless Internet access. Additional amenities at this hotel include concierge services, wedding services, and a television in a common area.<br/>Attractions : Radius are displayed to the nearest 0.1 mile and kilometer. <br /> <p>Museum of Church Art - 0.1 km / 0.1 mi <br /> Church of St. Barbara - 0.1 km / 0.1 mi <br /> Bunari Museum - 0.1 km / 0.1 mi <br /> Sibenik Aquarium - 0.2 km / 0.1 mi <br /> Cathedral of St. James - 0.2 km / 0.1 mi <br /> Benedictine Monastery of St. Lucia - 0.2 km / 0.2 mi <br /> Church of St. Krsevan - 0.3 km / 0.2 mi <br /> Church of St. Ivan - 0.3 km / 0.2 mi <br /> Uspenie Bogomatere Church - 0.3 km / 0.2 mi <br /> Church and Monastery of St.Francis - 0.3 km / 0.2 mi <br /> Medieval Mediterranean Garden of the Monastery of St. Lawrence - 0.3 km / 0.2 mi <br /> Church of Saint Spirit - 0.3 km / 0.2 mi <br /> Church of Gospe van Grada - 0.5 km / 0.3 mi <br /> St Michael's Fortress - 0.7 km / 0.4 mi <br /> Subicevac Fortress - 1 km / 0.6 mi <br /> </p><p>The nearest airports are:<br />Split Airport (SPU) - 60.1 km / 37.4 mi<br /> Zadar (ZAD) - 77.1 km / 47.9 mi<br /> </p><p>The preferred airport for Hotel Jadran is Zadar (ZAD). </p><br/>business_amenities : Featured amenities include a business center, dry cleaning/laundry services, and a 24-hour front desk. Planning an event in Sibenik? This hotel has 699 square feet (65 square meters) of space consisting of conference space and a meeting room. Guests may use a ferry terminal shuttle for a surcharge, and self parking (subject to charges) is available onsite.<br/>Dining : Enjoy a meal at the restaurant or snacks in the coffee shop/cafe. The hotel also offers room service (during limited hours). Quench your thirst with your favorite drink at the bar/lounge.<br/>HeadLine : In Sibenik (Sibenik Old Town)<br/>Rooms : Make yourself at home in one of the 57 air-conditioned guestrooms. Satellite television is provided for your entertainment. Private bathrooms with bathtubs feature complimentary toiletries and hair dryers. Conveniences include phones and desks, and housekeeping is provided daily.",
                          "currency": "EUR",
                          "country": "HR",
                          "city": "Sibenik",
                          "latitude": 43.73464,
                          "longitude": 15.88974,
                          "address": "Obala dr. Franje Tudmana 52",
                          "zip": "22000",
                          "main_photo": "https://liteapi-travel-static-data.s3.amazonaws.com/images/hotels/main/274412.jpg",
                          "thumbnail": "https://liteapi-travel-static-data.s3.amazonaws.com/images/hotels/main/274412.jpg",
                          "hotelTypeId": 204,
                          "chainId": 0,
                          "chain": "Not Available",
                          "stars": 3,
                          "facilityIds": [
                            1,
                            2,
                            3
                          ],
                          "accessibilityAttributes": {
                            "attributes": [
                              "entranceLeveled",
                              "restaurantWheelchairAccessible",
                              "cabinUnderSink"
                            ],
                            "showerChair": "",
                            "entranceType": "electric",
                            "petFriendly": "",
                            "rampAngle": 0,
                            "rampLength": 0,
                            "entranceDoorWidth": 0,
                            "roomMaxGuestsNumber": 0,
                            "distanceFromTheElevatorToTheAccessibleRoom": 0
                          }
                        },
                        {
                          "id": "lp50f7f",
                          "name": "Apartmani Ani",
                          "hotelDescription": "Location : When you stay at Apartmani Ani in Sibenik, you'll be near the beach and within a 5-minute drive of Krapanj Franciscan Monastery.  This apartment is 12.8 mi (20.6 km) from Krka National Park and 6 mi (9.7 km) from Dalmatian Ethno Village.<br/>Amenities : Take in the views from a terrace and a garden and make use of amenities such as complimentary wireless Internet access. This apartment also features tour/ticket assistance and barbecue grills.<br/>Attractions : Radius are displayed to the nearest 0.1 mile and kilometer. <br /> <p>Krapanj Franciscan Monastery - 4.2 km / 2.6 mi <br /> Dalmatian Ethno Village - 9.7 km / 6 mi <br /> Church of St. Barbara - 11 km / 6.8 mi <br /> Museum of Church Art - 11 km / 6.8 mi <br /> Bunari Museum - 11.1 km / 6.9 mi <br /> Church of Gospe van Grada - 11.9 km / 7.4 mi <br /> Church and Monastery of St.Francis - 12.1 km / 7.5 mi <br /> Sibenik Aquarium - 12.4 km / 7.7 mi <br /> Subicevac Fortress - 12.4 km / 7.7 mi <br /> Cathedral of St. James - 12.5 km / 7.8 mi <br /> Benedictine Monastery of St. Lucia - 12.5 km / 7.8 mi <br /> Beach Rtic - 13 km / 8.1 mi <br /> St. John's Fortress - 14 km / 8.7 mi <br /> Sokolarski Raptor Centre - 14 km / 8.7 mi <br /> Medieval Mediterranean Garden of the Monastery of St. Lawrence - 14.3 km / 8.9 mi <br /> </p><p>The preferred airport for Apartmani Ani is Split Airport (SPU) - 50.2 km / 31.2 mi </p><br/>business_amenities : Free self parking is available onsite.<br/>HeadLine : Near the beach<br/>Rooms : Make yourself at home in one of the 7 air-conditioned rooms featuring kitchens with full-sized refrigerators/freezers and stovetops. Rooms have private balconies or patios. Complimentary wireless Internet access keeps you connected, and satellite programming is available for your entertainment. Conveniences include safes and coffee/tea makers.",
                          "currency": "EUR",
                          "country": "HR",
                          "city": "Sibenik",
                          "latitude": 43.660413,
                          "longitude": 15.947561,
                          "address": "Bubanj I / Br. 10",
                          "zip": "22010",
                          "main_photo": "https://liteapi-travel-static-data.s3.amazonaws.com/images/hotels/main/331647.jpg",
                          "thumbnail": "https://liteapi-travel-static-data.s3.amazonaws.com/images/hotels/main/331647.jpg",
                          "hotelTypeId": 201,
                          "chainId": 0,
                          "chain": "Not Available",
                          "stars": 3,
                          "accessibilityAttributes": {
                            "attributes": [
                              "entranceLeveled",
                              "restaurantWheelchairAccessible",
                              "cabinUnderSink"
                            ],
                            "showerChair": "",
                            "entranceType": "electric",
                            "petFriendly": "",
                            "rampAngle": 0,
                            "rampLength": 0,
                            "entranceDoorWidth": 0,
                            "roomMaxGuestsNumber": 0,
                            "distanceFromTheElevatorToTheAccessibleRoom": 0
                          }
                        },
                        {
                          "id": "lp51261",
                          "name": "Villa Rosa",
                          "hotelDescription": "Location : Located in Sibenik, Villa Rosa is a 1-minute drive from Krapanj Franciscan Monastery and 6 minutes from Dalmatian Ethno Village.  This beach hotel is 10.7 mi (17.2 km) from Krka National Park and 4.6 mi (7.3 km) from Uspenie Bogomatere Church.<br/>Amenities : Take advantage of recreation opportunities such as a fitness center or take in the view from a terrace and a garden. This hotel also features complimentary wireless Internet access, tour/ticket assistance, and barbecue grills.<br/>Attractions : Radius are displayed to the nearest 0.1 mile and kilometer. <br /> <p>Krapanj Franciscan Monastery - 1.4 km / 0.9 mi <br /> Dalmatian Ethno Village - 5.4 km / 3.3 mi <br /> Uspenie Bogomatere Church - 7.3 km / 4.6 mi <br /> Church of Gospe van Grada - 7.6 km / 4.7 mi <br /> Church of St. Barbara - 7.6 km / 4.7 mi <br /> Museum of Church Art - 7.7 km / 4.8 mi <br /> Bunari Museum - 7.7 km / 4.8 mi <br /> Church of Saint Spirit - 7.8 km / 4.9 mi <br /> Church of St. Ivan - 7.9 km / 4.9 mi <br /> Sibenik Aquarium - 8.1 km / 5 mi <br /> Cathedral of St. James - 8.2 km / 5.1 mi <br /> Subicevac Fortress - 8.4 km / 5.2 mi <br /> St. John's Fortress - 8.7 km / 5.4 mi <br /> Church and Monastery of St.Francis - 8.7 km / 5.4 mi <br /> Benedictine Monastery of St. Lucia - 9.2 km / 5.7 mi <br /> </p><p>The nearest major airport is Split Airport (SPU) - 52.4 km / 32.6 mi</p><br/>business_amenities : Featured amenities include dry cleaning/laundry services and laundry facilities. Free self parking is available onsite.<br/>Dining : Enjoy a meal at the restaurant, or stay in and take advantage of the hotel's room service. Buffet breakfasts are available daily from 7:30 AM to 9:00 AM for a fee.<br/>HeadLine : Near Krapanj Franciscan Monastery<br/>Rooms : Make yourself at home in one of the 8 guestrooms, featuring kitchenettes with refrigerators and microwaves. Rooms have private balconies. Complimentary wireless Internet access is available to keep you connected. Conveniences include desks and coffee/tea makers, and housekeeping is provided daily.",
                          "currency": "EUR",
                          "country": "HR",
                          "city": "Sibenik",
                          "latitude": 43.678974,
                          "longitude": 15.917649,
                          "address": "Krapanjskih Spuzvara 40",
                          "zip": "22010",
                          "main_photo": "https://liteapi-travel-static-data.s3.amazonaws.com/images/hotels/main/332385.jpg",
                          "thumbnail": "https://liteapi-travel-static-data.s3.amazonaws.com/images/hotels/main/332385.jpg",
                          "hotelTypeId": 204,
                          "chainId": 0,
                          "chain": "Not Available",
                          "stars": 2.5,
                          "accessibilityAttributes": {
                            "attributes": [
                              "entranceLeveled",
                              "restaurantWheelchairAccessible",
                              "cabinUnderSink"
                            ],
                            "showerChair": "",
                            "entranceType": "electric",
                            "petFriendly": "",
                            "rampAngle": 0,
                            "rampLength": 0,
                            "entranceDoorWidth": 0,
                            "roomMaxGuestsNumber": 0,
                            "distanceFromTheElevatorToTheAccessibleRoom": 0
                          }
                        }
                      ],
                      "hotelIds": [
                        "lp42fec",
                        "lp50f7f",
                        "lp51261"
                      ],
                      "total": 1004
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
                          "description": "Message providing context about the error"
                        }
                      }
                    }
                  }
                },
                "examples": {
                  "countryCode is required": {
                    "value": {
                      "error": {
                        "code": 4000,
                        "description": "countryCode is required",
                        "message": "bad request"
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
                        "message": {
                          "type": "string",
                          "description": "Message providing context about the error"
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