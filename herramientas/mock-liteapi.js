// Simulador local de LiteAPI (solo pruebas): responde con el formato de la documentación oficial.
const http = require("http");
const img = (s, w = 1200, h = 800) => `https://picsum.photos/seed/${s}/${w}/${h}`;
const hotels = Array.from({ length: 14 }, (_, i) => ({
  id: "lp" + (1000 + i), name: ["Grand Palladium", "Riu Republica", "Barceló Bávaro", "Iberostar Selection", "Majestic Elegance", "Dreams Royal Beach", "Secrets Cap Cana", "Hard Rock", "Paradisus Palma Real", "Excellence Punta Cana", "Lopesan Costa Bávaro", "Hyatt Zilara", "Sanctuary Cap Cana", "Occidental Caribe"][i] + " Resort",
  stars: [5, 5, 5, 5, 5, 4.5, 5, 5, 5, 5, 4, 5, 5, 4][i], rating: [9.1, 8.7, 8.9, 9.0, 9.2, 8.4, 9.3, 8.6, 8.8, 9.4, 8.2, 9.0, 9.1, 7.9][i],
  reviewCount: 500 + i * 137, address: "Playa Bávaro, km " + (i + 2), main_photo: img("h" + i), thumbnail: img("h" + i, 600, 400),
}));
const hotel = id => ({ data: {
  id, name: (hotels.find(h => h.id === id) || hotels[0]).name, hotelDescription: "<p><strong>Ubicación:</strong> frente a la playa de arena blanca de Bávaro, a 25 minutos del aeropuerto.</p><p><strong>Habitaciones:</strong> suites amplias con balcón, minibar y Wi-Fi gratis.</p><p>El resort tiene 6 piscinas, 9 restaurantes, spa y club infantil.</p>",
  hotelImportantInformation: "Se requiere documento de identidad al llegar. El depósito de garantía se devuelve al salir.",
  checkinCheckoutTimes: { checkin_start: "15:00", checkout: "12:00" },
  hotelImages: Array.from({ length: 10 }, (_, k) => ({ url: img(id + k, 600, 400), urlHd: img(id + k), caption: k ? "Vista " + k : "Piscina principal", order: k, defaultImage: k === 0 })),
  country: "do", city: "Punta Cana", starRating: 5, location: { latitude: 18.68, longitude: -68.42 }, address: "Playa Bávaro",
  facilities: ["Piscina", "Spa", "Gimnasio", "Wi-Fi gratis", "Club infantil", "Bar en la piscina", "Restaurante a la carta", "Estacionamiento"].map((n, k) => ({ facilityId: k, name: n })),
  rooms: [1, 2, 3].map(k => ({ id: k, roomName: ["Junior Suite vista jardín", "Junior Suite vista mar", "Suite familiar"][k - 1], roomSizeSquare: 40 + k * 10, roomSizeUnit: "m²", maxOccupancy: 2 + k, bedTypes: [{ quantity: 1, bedType: "cama king" }], roomAmenities: ["Aire acondicionado", "Minibar", "Caja fuerte", "Balcón", "Wi-Fi gratis"].map((name, a) => ({ amenitiesId: a, name })), description: "Suite con balcón privado, baño con ducha de lluvia y zona de estar.", photos: [1, 2, 3, 4].map(q => ({ url: img("r" + id + k + q, 600, 400), hd_url: img("r" + id + k + q), imageDescription: q === 1 ? "Habitación" : q === 2 ? "Baño" : "" })) })),
  poi: [{ name: "Aeropuerto de Punta Cana", distanceKm: 22.4 }, { name: "Playa Bávaro", distanceKm: 0.1 }, { name: "Downtown Punta Cana", distanceKm: 9.8 }],
  rating: 9.1, reviewCount: 2310, chain: "Palladium", airportCode: "PUJ",
  sentiment_analysis: { pros: ["Playa muy limpia", "Comida variada y de buena calidad", "Personal amable"], cons: ["Wi-Fi lento en algunas zonas"], categories: [{ name: "Limpieza", rating: 9.2 }, { name: "Servicio", rating: 9.0 }, { name: "Ubicación", rating: 9.5 }] },
} });
const tours = Array.from({ length: 8 }, (_, i) => ({ id: 903300 + i, title: ["Isla Saona en catamarán", "Buggies y cenote", "Snorkel en arrecife", "Santo Domingo colonial", "Parasailing", "Cayo Levantado", "Hoyo Azul y Scape Park", "Pesca deportiva"][i],
  description: "Una experiencia guiada con transporte desde tu hotel.\n\nIncluye almuerzo y bebidas.", overallRating: 4.4 + (i % 5) / 10, numberOfRatings: 300 + i * 51, hasPickUp: i % 2 === 0,
  pictures: [1, 2, 3, 4].map(k => ({ id: k, url: img("t" + i + k), verified: true })), durations: [{ value: [8, 3, 2, 10, 1, 9, 6, 4][i], unit: "hours" }], categories: [{ id: 1, name: ["Excursión", "Aventura", "Acuático", "Cultural"][i % 4] }],
  highlights: ["Guía en español", "Transporte incluido"], included: "Transporte, guía, almuerzo", excluded: "Propinas", placeDetails: "Te recogemos en el lobby de tu hotel.",
  itemsToBring: ["Protector solar", "Traje de baño"], notSuitableFor: ["Embarazadas"], coordinates: { lat: 18.6, long: -68.4 }, locations: [{ city: "Punta Cana", country: "DO" }],
  cancellationPolicy: { description: "Cancelación gratis hasta 24 horas antes." }, price: { amount: 35 + i * 12.5, currency: "USD" }, priceDescription: "por persona", itineraries: [{ dayNumber: 1, items: [{ title: "Salida del hotel", startTime: "07:00" }, { title: "Navegación en catamarán", duration: { value: 2, unit: "hours" } }, { title: "Almuerzo en la playa" }] }] }));
http.createServer((req, res) => {
  const u = new URL(req.url, "http://x"); const send = (c, j) => { res.writeHead(c, { "Content-Type": "application/json" }); res.end(JSON.stringify(j)); };
  if (req.headers["x-api-key"] !== "clave-de-prueba-local") return send(401, { error: "unauthorized" });
  if (u.pathname === "/v3.0/data/hotelTypes") return send(200, { data: [{ id: 204, name: "Hotels" }, { id: 201, name: "Apartments" }, { id: 208, name: "Resorts" }, { id: 203, name: "Hostels" }, { id: 212, name: "Bed and breakfasts" }] });
  if (u.pathname === "/v3.0/data/hotels") {
    const full = hotels.map((h, i) => ({ ...h, hotelTypeId: [208, 204, 208, 201, 204, 212, 208][i % 7], latitude: 18.68 + (i % 5) * 0.02, longitude: -68.42 + (i % 3) * 0.03, reviewCount: h.reviewCount }));
    const ids = (u.searchParams.get("hotelIds") || "").split(",").filter(Boolean);
    if (ids.length) return send(200, { data: full.filter(h => ids.includes(h.id)) });
    if (u.searchParams.get("placeId") === "ChIJriu") return send(200, { data: [full[3], full[1], full[5]] });
    return send(200, { data: full });
  }
  if (u.pathname === "/v3.0/data/hotel") return hotels.some(h => h.id === u.searchParams.get("hotelId")) ? send(200, hotel(u.searchParams.get("hotelId"))) : send(404, { error: "not found" });
  if (u.pathname === "/v3.0/data/places") { const q = (u.searchParams.get("textQuery") || "").toLowerCase(); return send(200, { data: [["ChIJpunta", "Punta Cana", "La Altagracia, República Dominicana"], ["ChIJcancun", "Cancún", "Quintana Roo, México"], ["ChIJaruba", "Aruba", "Aruba"], ["ChIJctg", "Cartagena", "Bolívar, Colombia"], ["ChIJriu", "Riu Republica Resort", "Punta Cana, República Dominicana"]].filter(x => x[1].toLowerCase().includes(q.slice(0, 3))).map(([placeId, displayName, formattedAddress]) => ({ placeId, displayName, formattedAddress, types: placeId === "ChIJriu" ? ["lodging", "hotel"] : ["locality"] })) }); }
  const pm = u.pathname.match(/^\/v3\.0\/data\/places\/(.+)$/);
  if (pm) return send(200, { data: { displayName: "Punta Cana", location: { latitude: 18.68, longitude: -68.42 } } });
  if (u.pathname === "/v3.0/data/flights/airports") { const q = (u.searchParams.get("q") || "").toLowerCase(); const all = [["BOG", "El Dorado", "Bogotá", "Colombia"], ["PUJ", "Aeropuerto de Punta Cana", "Punta Cana", "República Dominicana"], ["CUN", "Aeropuerto de Cancún", "Cancún", "México"], ["MDE", "José María Córdova", "Medellín", "Colombia"]];
    return send(200, { data: [{ airports: all.filter(a => a.join(" ").toLowerCase().includes(q)).map(([iata, name, city, country]) => ({ iata, name, city, country })) }] }); }
  if (u.pathname === "/v3.0/flights/rates" && req.method === "POST") {
    let b = ""; req.on("data", c => b += c); req.on("end", () => {
      const q = JSON.parse(b || "{}"), pax = q.adults + (q.children || 0);
      const journeys = Array.from({ length: 6 }, (_, k) => {
        const air = [["Avianca", "AV"], ["Copa Airlines", "CM"], ["Wingo", "P5"], ["LATAM", "LA"]][k % 4], stops = k % 3 === 0 ? 0 : 1;
        const segs = q.legs.flatMap(l => { const h = 6 + k * 2; const dep = `${l.date}T${String(h).padStart(2, "0")}:15:00`;
          return stops ? [{ direction: l.direction, originCode: l.origin, destinationCode: "PTY", departureTime: dep, arrivalTime: `${l.date}T${String(h + 2).padStart(2, "0")}:00:00`, carrier: { marketingName: air[0], marketingCode: air[1], marketingLogo: `https://picsum.photos/seed/${air[1]}/60/60` }, flight: { marketingNumber: String(100 + k) } },
            { direction: l.direction, originCode: "PTY", destinationCode: l.destination, departureTime: `${l.date}T${String(h + 3).padStart(2, "0")}:00:00`, arrivalTime: `${l.date}T${String(h + 5).padStart(2, "0")}:10:00`, carrier: { marketingName: air[0], marketingCode: air[1], marketingLogo: `https://picsum.photos/seed/${air[1]}/60/60` }, flight: { marketingNumber: String(200 + k) } }]
            : [{ direction: l.direction, originCode: l.origin, destinationCode: l.destination, departureTime: dep, arrivalTime: `${l.date}T${String(h + 3).padStart(2, "0")}:05:00`, carrier: { marketingName: air[0], marketingCode: air[1], marketingLogo: `https://picsum.photos/seed/${air[1]}/60/60` }, flight: { marketingNumber: String(300 + k) } }]; });
        const per = 290 + k * 47 + (q.cabinClass === "BUSINESS" ? 600 : 0);
        return { journeyKey: "j" + k, segments: segs, legDurations: q.legs.map(l => ({ direction: l.direction, duration: { minutes: stops ? 295 : 170 } })),
          offers: [{ pricing: { display: { total: per * pax, currency: "USD", perPassenger: { adult: { total: per } } } }, baggage: { hasCarryOnBag: true, hasCheckedBag: k % 2 === 1 }, terms: { refundable: k === 5, changeable: k > 2 }, fare: { family: ["Basic", "Classic", "Flex"][k % 3], seatsRemaining: 3 + k } }] };
      });
      send(200, { data: [{ journeys }] });
    }); return;
  }
  if (u.pathname === "/v3.0/hotels/rates" && req.method === "POST") {
    let b = ""; req.on("data", c => b += c); req.on("end", () => {
      const q = JSON.parse(b || "{}"), n = Math.max(1, Math.round((new Date(q.checkout) - new Date(q.checkin)) / 864e5)), ad = (q.occupancies || []).reduce((a, o) => a + o.adults, 0);
      const data = (q.hotelIds || (q.placeId ? hotels.map(h => h.id) : [])).map((id, k) => ({ hotelId: id, roomTypes: [["Junior Suite vista jardín", k % 3 ? "AI" : "HB", k % 2 ? "NRFN" : "RFN", 1], ["Junior Suite vista mar", "AI", "NRFN", 1.18], ["Suite familiar", "AI", "RFN", 1.4], ["Habitación estándar", k % 4 ? "BB" : "RO", k % 3 ? "RFN" : "NRFN", 0.8 + (k % 5) * 0.15]].map(([name, board, ref, f]) => ({
        offerId: "x", rates: (q.occupancies || [{}]).map(() => ({ name, mappedRoomId: q.roomMapping ? ({ "Junior Suite vista jardín": 1, "Junior Suite vista mar": 2, "Suite familiar": 3 })[name] : undefined, boardType: board, boardName: board, commission: [{ amount: Math.round((140 + k * 23) * f * n * (ad / 2) / (q.occupancies || [1]).length * 8) / 100, currency: "USD" }], retailRate: { total: [{ amount: Math.round((140 + k * 23) * f * n * (ad / 2) / (q.occupancies || [1]).length * 100) / 100, currency: "USD" }], suggestedSellingPrice: [{ amount: Math.round((140 + k * 23) * f * n * (ad / 2) / (q.occupancies || [1]).length * 118) / 100, currency: "USD" }], taxesAndFees: [{ included: false, description: "Resort fee", amount: 12 * n, currency: "USD" }, { included: true, description: "IVA", amount: 20, currency: "USD" }] }, cancellationPolicies: { refundableTag: ref } })) })) }));
      send(200, q.includeHotelData ? { data, hotels: hotels.map(h => ({ id: h.id, name: h.name, main_photo: h.main_photo, thumbnail: h.thumbnail, stars: h.stars, rating: h.rating, review_count: h.reviewCount, address: h.address, city_name: "Punta Cana" })) } : { data });
    }); return;
  }
  if (u.pathname === "/v3.0/experiences/tours") return send(200, { data: { meta: { totalCount: tours.length }, tours } });
  const m = u.pathname.match(/^\/v3\.0\/experiences\/tours\/(\d+)$/);
  if (m) { const t = tours.find(x => String(x.id) === m[1]); return t ? send(200, { data: t }) : send(404, {}); }
  send(404, { error: "no route" });
}).listen(8771, "127.0.0.1");
