<?php
// includes/data.php — ClicKet Mock Event Data

function posterUrl(string $category, int $seed): string {
    $base = match($category) {
        'concert'  => 1000,
        'theater'  => 2000,
        'sports'   => 3000,
        'featured' => 4000,
        default    => 5000,
    };
    $id = $base + $seed;
    return "https://picsum.photos/seed/{$id}/400/600";
}

function landscapeUrl(string $category, int $seed): string {
    $base = match($category) {
        'concert'  => 1000,
        'theater'  => 2000,
        'sports'   => 3000,
        'featured' => 4000,
        default    => 5000,
    };
    $id = $base + $seed;
    return "https://picsum.photos/seed/{$id}/1200/650";
}

// ─── FEATURED EVENTS ─────────────────────────────────────
$featured_events = [
  ['title'=>'BLACKPINK World Tour','sub'=>'Live in Manila 2025','category'=>'Concert','venue'=>'Philippine Arena','date'=>'Nov 15, 2025','price'=>'₱3,500','poster'=>posterUrl('featured',1)],
  ['title'=>'Miss Saigon','sub'=>'Broadway Revisited','category'=>'Theater','venue'=>'Newport Performing Arts Theater','date'=>'Oct 22, 2025','price'=>'₱1,800','poster'=>posterUrl('featured',2)],
  ['title'=>'PBA Finals','sub'=>'Game 7 — The Decider','category'=>'Sports','venue'=>'Araneta Coliseum','date'=>'Dec 5, 2025','price'=>'₱600','poster'=>posterUrl('featured',3)],
  ['title'=>'Bruno Mars','sub'=>'24K Magic Live!','category'=>'Concert','venue'=>'MOA Arena','date'=>'Jan 10, 2026','price'=>'₱5,000','poster'=>posterUrl('featured',4)],
  ['title'=>'Hamilton','sub'=>'The Musical','category'=>'Theater','venue'=>'Newport Performing Arts Theater','date'=>'Feb 14, 2026','price'=>'₱2,500','poster'=>posterUrl('featured',5)],
  ['title'=>'FIBA Asia Cup','sub'=>'Philippines vs. Lebanon','category'=>'Sports','venue'=>'Philippine Arena','date'=>'Mar 2, 2026','price'=>'₱1,200','poster'=>posterUrl('featured',6)],
  ['title'=>'Taylor Swift','sub'=>'The Eras Tour Manila','category'=>'Concert','venue'=>'Philippine Arena','date'=>'Apr 19, 2026','price'=>'₱6,800','poster'=>posterUrl('featured',7)],
];

// ─── CONCERT EVENTS ──────────────────────────────────────
$concert_events = [
  ['title'=>'BLACKPINK World Tour','artist'=>'BLACKPINK','venue'=>'Philippine Arena','date'=>'Nov 15, 2025','price'=>'₱3,500','rating'=>5,'type'=>'International'],
  ['title'=>'Bruno Mars Live','artist'=>'Bruno Mars','venue'=>'MOA Arena','date'=>'Jan 10, 2026','price'=>'₱5,000','rating'=>5,'type'=>'International'],
  ['title'=>'Taylor Swift Eras Tour','artist'=>'Taylor Swift','venue'=>'Philippine Arena','date'=>'Apr 19, 2026','price'=>'₱6,800','rating'=>5,'type'=>'International'],
  ['title'=>'Ben&Ben Grand Concert','artist'=>'Ben&Ben','venue'=>'Araneta Coliseum','date'=>'Dec 20, 2025','price'=>'₱1,200','rating'=>5,'type'=>'Local'],
  ['title'=>'SB19 PAGTATAG! Tour','artist'=>'SB19','venue'=>'Philsports Arena','date'=>'Nov 30, 2025','price'=>'₱1,500','rating'=>5,'type'=>'Local'],
  ['title'=>'Coldplay Music of the Spheres','artist'=>'Coldplay','venue'=>'Philippine Arena','date'=>'Mar 8, 2026','price'=>'₱7,500','rating'=>5,'type'=>'International'],
  ['title'=>'The Eraserheads Reunion','artist'=>'Eraserheads','venue'=>'Araneta Coliseum','date'=>'Feb 7, 2026','price'=>'₱2,000','rating'=>5,'type'=>'Local'],
  ['title'=>'Ed Sheeran Mathematics','artist'=>'Ed Sheeran','venue'=>'MOA Arena','date'=>'May 22, 2026','price'=>'₱5,500','rating'=>5,'type'=>'International'],
  ['title'=>'December Avenue Live','artist'=>'December Avenue','venue'=>'Kia Theater','date'=>'Jan 25, 2026','price'=>'₱900','rating'=>4,'type'=>'Local'],
  ['title'=>'Bamboo: Rock for Life','artist'=>'Bamboo','venue'=>'SM Mall of Asia Grounds','date'=>'Dec 31, 2025','price'=>'₱800','rating'=>4,'type'=>'Local'],
];

// ─── THEATER EVENTS ──────────────────────────────────────
$theater_events = [
  ['title'=>'Miss Saigon','company'=>'Broadway Manila','venue'=>'Newport Performing Arts Theater','date'=>'Oct 22, 2025','price'=>'₱1,800','rating'=>5,'type'=>'Musical'],
  ['title'=>'Hamilton','company'=>'Original Broadway Cast','venue'=>'Newport Performing Arts Theater','date'=>'Feb 14, 2026','price'=>'₱2,500','rating'=>5,'type'=>'Musical'],
  ['title'=>'Ang Huling El Bimbo','company'=>'Repertory Philippines','venue'=>'RCBC Theater','date'=>'Nov 8, 2025','price'=>'₱1,400','rating'=>5,'type'=>'Musical'],
  ['title'=>'The Phantom of the Opera','company'=>'Manila Broadway','venue'=>'Newport Performing Arts Theater','date'=>'Dec 12, 2025','price'=>'₱2,200','rating'=>5,'type'=>'Musical'],
  ['title'=>'Mula sa Buwan','company'=>'Tanghalang Pilipino','venue'=>'CCP Main Theater','date'=>'Jan 17, 2026','price'=>'₱1,000','rating'=>5,'type'=>'Musical'],
  ['title'=>'Les Misérables','company'=>'Manila Grand Opera','venue'=>'Newport Performing Arts Theater','date'=>'Mar 20, 2026','price'=>'₱3,000','rating'=>5,'type'=>'Musical'],
  ['title'=>'Noli Me Tangere Opera','company'=>'Opera Manila','venue'=>'CCP Main Theater','date'=>'Jun 12, 2026','price'=>'₱800','rating'=>4,'type'=>'Opera'],
  ['title'=>'Rak of Aegis','company'=>'PETA Theater','venue'=>'PETA Theater Center','date'=>'Nov 28, 2025','price'=>'₱900','rating'=>5,'type'=>'Musical'],
  ['title'=>'A Chorus Line','company'=>'Repertory Philippines','venue'=>'Onstage Greenbelt 1','date'=>'Feb 28, 2026','price'=>'₱1,200','rating'=>4,'type'=>'Musical'],
  ['title'=>'The Lion King','company'=>'Disney Theatrical','venue'=>'Newport Performing Arts Theater','date'=>'May 5, 2026','price'=>'₱3,500','rating'=>5,'type'=>'Musical'],
];

// ─── SPORTS EVENTS ───────────────────────────────────────
$sports_events = [
  ['title'=>'PBA Finals Game 7','league'=>'PBA','venue'=>'Araneta Coliseum','date'=>'Dec 5, 2025','price'=>'₱600','rating'=>5,'type'=>'Basketball'],
  ['title'=>'FIBA Asia Cup QF','league'=>'FIBA','venue'=>'Philippine Arena','date'=>'Mar 2, 2026','price'=>'₱1,200','rating'=>5,'type'=>'Basketball'],
  ['title'=>'Pacquiao Exhibition Bout','league'=>'WBA','venue'=>'MOA Arena','date'=>'Jan 30, 2026','price'=>'₱2,000','rating'=>5,'type'=>'Boxing'],
  ['title'=>'UAAP Season 88 Finals','league'=>'UAAP','venue'=>'Araneta Coliseum','date'=>'Nov 22, 2025','price'=>'₱500','rating'=>5,'type'=>'Basketball'],
  ['title'=>'Azkals vs Thailand','league'=>'AFF Championship','venue'=>'Rizal Memorial Stadium','date'=>'Dec 18, 2025','price'=>'₱300','rating'=>4,'type'=>'Football'],
  ['title'=>'WWE Live Manila','league'=>'WWE','venue'=>'Philippine Arena','date'=>'Feb 20, 2026','price'=>'₱1,800','rating'=>5,'type'=>'Wrestling'],
  ['title'=>'Philippine Open Tennis','league'=>'ATP Challenger','venue'=>'Rizal Memorial Tennis','date'=>'Mar 15, 2026','price'=>'₱350','rating'=>4,'type'=>'Tennis'],
  ['title'=>'Ironman 70.3 Cebu','league'=>'Ironman','venue'=>'Cebu City','date'=>'Apr 5, 2026','price'=>'₱450','rating'=>4,'type'=>'Triathlon'],
  ['title'=>'NCAA Season 100 Finals','league'=>'NCAA Philippines','venue'=>'Filoil EcoOil Centre','date'=>'Nov 10, 2025','price'=>'₱400','rating'=>5,'type'=>'Basketball'],
  ['title'=>'PVL AFC Final','league'=>'PVL','venue'=>'PhilSports Arena','date'=>'May 18, 2026','price'=>'₱550','rating'=>5,'type'=>'Volleyball'],
];
