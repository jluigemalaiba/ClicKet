<?php
// includes/data.php - ClicKet Mock Event Data

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

function eventDetailUrl(string $category, int $index): string {
    $categoryKey = match ($category) {
        'concert' => 'concerts',
        'sports' => 'sports',
        default => $category,
    };

    return 'show.php?event=' . rawurlencode($categoryKey . '-' . ($index + 1));
}

function eventDetailUrlByTitle(string $title): string {
    global $concert_events, $theater_events, $sports_events;

    $catalogs = [
        'concerts' => $concert_events ?? [],
        'theater' => $theater_events ?? [],
        'sports' => $sports_events ?? [],
    ];

    foreach ($catalogs as $category => $events) {
        foreach ($events as $index => $event) {
            if (strcasecmp((string) ($event['title'] ?? ''), $title) === 0) {
                return eventDetailUrl($category, $index);
            }
        }
    }

    return 'events.php?search=' . rawurlencode($title);
}

// Featured events
$featured_events = [
  ['title'=>'BTS Permission to Dance Manila','sub'=>'International','category'=>'Concert','venue'=>'MOA Arena','date'=>'Nov 15, 2025','price'=>'PHP 12,000','poster'=>posterUrl('featured',1)],
  ['title'=>'Miss Saigon','sub'=>'Broadway Revisited','category'=>'Theater','venue'=>'Newport Performing Arts Theater','date'=>'Oct 22, 2025','price'=>'PHP 1,800','poster'=>posterUrl('featured',2)],
  ['title'=>'PBA Finals','sub'=>'Game 7 - The Decider','category'=>'Sports','venue'=>'Smart Araneta Coliseum','date'=>'Dec 5, 2025','price'=>'PHP 600','poster'=>posterUrl('featured',3)],
  ['title'=>'BLACKPINK Born Pink Encore','sub'=>'Arena tour','category'=>'Concert','venue'=>'MOA Arena','date'=>'Jan 10, 2026','price'=>'PHP 9,500','poster'=>posterUrl('featured',4)],
  ['title'=>'Hamilton','sub'=>'The Musical','category'=>'Theater','venue'=>'Newport Performing Arts Theater','date'=>'Feb 14, 2026','price'=>'PHP 2,500','poster'=>posterUrl('featured',5)],
  ['title'=>'FIBA Asia Cup','sub'=>'Philippines vs. Lebanon','category'=>'Sports','venue'=>'Philippine Arena','date'=>'Mar 2, 2026','price'=>'PHP 1,200','poster'=>posterUrl('featured',6)],
  ['title'=>'Taylor Swift The Eras Tour Manila','sub'=>'International','category'=>'Concert','venue'=>'Philippine Arena','date'=>'Apr 19, 2026','price'=>'PHP 14,500','poster'=>posterUrl('featured',7)],
];

// Concert events
$concert_events = [
  ['title'=>'BTS Permission to Dance Manila','artist'=>'BTS','venue'=>'MOA Arena','date'=>'Nov 15, 2025','price'=>'PHP 12,000','rating'=>5,'type'=>'International'],
  ['title'=>'BLACKPINK Born Pink Encore','artist'=>'BLACKPINK','venue'=>'MOA Arena','date'=>'Jan 10, 2026','price'=>'PHP 9,500','rating'=>5,'type'=>'International'],
  ['title'=>'Bruno Mars 24K Magic Live','artist'=>'Bruno Mars','venue'=>'MOA Arena','date'=>'Jan 24, 2026','price'=>'PHP 8,500','rating'=>5,'type'=>'International'],
  ['title'=>'Ariana Grande Eternal Sunshine Live','artist'=>'Ariana Grande','venue'=>'MOA Arena','date'=>'Feb 28, 2026','price'=>'PHP 10,500','rating'=>5,'type'=>'International'],
  ['title'=>'Taylor Swift The Eras Tour Manila','artist'=>'Taylor Swift','venue'=>'Philippine Arena','date'=>'Apr 19, 2026','price'=>'PHP 14,500','rating'=>5,'type'=>'International'],
  ['title'=>'Coldplay Music of the Spheres','artist'=>'Coldplay','venue'=>'Philippine Arena','date'=>'Mar 8, 2026','price'=>'PHP 7,500','rating'=>5,'type'=>'International'],
  ['title'=>'Ed Sheeran Mathematics','artist'=>'Ed Sheeran','venue'=>'Philippine Arena','date'=>'May 22, 2026','price'=>'PHP 6,500','rating'=>5,'type'=>'International'],
  ['title'=>'Billie Eilish Hit Me Hard and Soft','artist'=>'Billie Eilish','venue'=>'Philippine Arena','date'=>'Jun 20, 2026','price'=>'PHP 8,800','rating'=>5,'type'=>'International'],
  ['title'=>'Ben&Ben Grand Concert','artist'=>'Ben&Ben','venue'=>'Smart Araneta Coliseum','date'=>'Dec 20, 2025','price'=>'PHP 1,200','rating'=>5,'type'=>'Local'],
  ['title'=>'The Eraserheads Reunion','artist'=>'Eraserheads','venue'=>'Smart Araneta Coliseum','date'=>'Feb 7, 2026','price'=>'PHP 2,000','rating'=>5,'type'=>'Local'],
  ['title'=>'SB19 PAGTATAG! Tour','artist'=>'SB19','venue'=>'Philsports Arena','date'=>'Nov 30, 2025','price'=>'PHP 1,500','rating'=>5,'type'=>'Local'],
  ['title'=>'December Avenue Live','artist'=>'December Avenue','venue'=>'Samsung Hall','date'=>'Jan 25, 2026','price'=>'PHP 900','rating'=>4,'type'=>'Local'],
  ['title'=>'Bamboo: Rock for Life','artist'=>'Bamboo','venue'=>'Nuvali','date'=>'Dec 31, 2025','price'=>'PHP 800','rating'=>4,'type'=>'Local'],
];

// Theater events
$theater_events = [
  ['title'=>'Miss Saigon','company'=>'Broadway Manila','venue'=>'Newport Performing Arts Theater','date'=>'Oct 22, 2025','price'=>'PHP 1,800','rating'=>5,'type'=>'Musical'],
  ['title'=>'Hamilton','company'=>'Original Broadway Cast','venue'=>'Newport Performing Arts Theater','date'=>'Feb 14, 2026','price'=>'PHP 2,500','rating'=>5,'type'=>'Musical'],
  ['title'=>'Ang Huling El Bimbo','company'=>'Repertory Philippines','venue'=>'Solaire Resort Entertainment City','date'=>'Nov 8, 2025','price'=>'PHP 1,400','rating'=>5,'type'=>'Musical'],
  ['title'=>'The Phantom of the Opera','company'=>'Manila Broadway','venue'=>'Newport Performing Arts Theater','date'=>'Dec 12, 2025','price'=>'PHP 2,200','rating'=>5,'type'=>'Musical'],
  ['title'=>'Mula sa Buwan','company'=>'Tanghalang Pilipino','venue'=>'Tanghalang Pilipino','date'=>'Jan 17, 2026','price'=>'PHP 1,000','rating'=>5,'type'=>'Musical'],
  ['title'=>'Les Miserables','company'=>'Manila Grand Opera','venue'=>'Newport Performing Arts Theater','date'=>'Mar 20, 2026','price'=>'PHP 3,000','rating'=>5,'type'=>'Musical'],
  ['title'=>'Noli Me Tangere Opera','company'=>'Opera Manila','venue'=>'Metropolitan Theater','date'=>'Jun 12, 2026','price'=>'PHP 800','rating'=>4,'type'=>'Opera'],
  ['title'=>'Rak of Aegis','company'=>'PETA Theater','venue'=>'Resorts World Manila','date'=>'Nov 28, 2025','price'=>'PHP 900','rating'=>5,'type'=>'Musical'],
  ['title'=>'A Chorus Line','company'=>'Repertory Philippines','venue'=>'Solaire Resort Entertainment City','date'=>'Feb 28, 2026','price'=>'PHP 1,200','rating'=>4,'type'=>'Musical'],
  ['title'=>'The Lion King','company'=>'Disney Theatrical','venue'=>'Newport Performing Arts Theater','date'=>'May 5, 2026','price'=>'PHP 3,500','rating'=>5,'type'=>'Musical'],
];

// Sports events
$sports_events = [
  ['title'=>'PBA Finals Game 7','league'=>'PBA','venue'=>'Smart Araneta Coliseum','date'=>'Dec 5, 2025','rating'=>5,'type'=>'Basketball'],
  ['title'=>'FIBA Asia Cup QF','league'=>'FIBA','venue'=>'Philippine Arena','date'=>'Mar 2, 2026','rating'=>5,'type'=>'Basketball'],
  ['title'=>'Pacquiao Exhibition Bout','league'=>'WBA','venue'=>'MOA Arena','date'=>'Jan 30, 2026','rating'=>5,'type'=>'Boxing'],
  ['title'=>'UAAP Season 88 Finals','league'=>'UAAP','venue'=>'Smart Araneta Coliseum','date'=>'Nov 22, 2025','rating'=>5,'type'=>'Basketball'],
  ['title'=>'Azkals vs Thailand','league'=>'AFF Championship','venue'=>'Ninoy Aquino Stadium and Rizal Memorial','date'=>'Dec 18, 2025','rating'=>4,'type'=>'Football'],
  ['title'=>'WWE Live Manila','league'=>'WWE','venue'=>'Philippine Arena','date'=>'Feb 20, 2026','rating'=>5,'type'=>'Wrestling'],
  ['title'=>'Philippine Open Tennis','league'=>'ATP Challenger','venue'=>'Ninoy Aquino Stadium and Rizal Memorial','date'=>'Mar 15, 2026','rating'=>4,'type'=>'Tennis'],
  ['title'=>'Ironman 70.3 Laguna','league'=>'Ironman','venue'=>'Nuvali','date'=>'Apr 5, 2026','rating'=>4,'type'=>'Triathlon'],
  ['title'=>'NCAA Season 100 Finals','league'=>'NCAA Philippines','venue'=>'Filoil EcoOil Centre','date'=>'Nov 10, 2025','rating'=>5,'type'=>'Basketball'],
  ['title'=>'PVL AFC Final','league'=>'PVL','venue'=>'Philsports Arena','date'=>'May 18, 2026','rating'=>5,'type'=>'Volleyball'],
  ['title'=>'MPBL Pasay Invitational','league'=>'MPBL','venue'=>'Cuneta Astrodome','date'=>'Feb 9, 2026','rating'=>4,'type'=>'Basketball'],
  ['title'=>'Muntinlupa Hoopfest Finals','league'=>'City Hoops','venue'=>'Muntinlupa Sports Center','date'=>'Mar 28, 2026','rating'=>4,'type'=>'Basketball'],
];
