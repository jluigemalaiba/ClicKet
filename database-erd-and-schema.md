# CLICKET Final Database ERD and Schema Documentation

## Overview

This document defines the finalized database design for the CLICKET Event Ticketing and Venue Management System.

The system supports customer account management, staff administration, venue and seating layout management, event scheduling, seat reservations, order processing, payment verification, ticket issuance, ticket validation, venue check-in operations, favorites management, auditing, and archive management.

The finalized design prioritizes operational ticketing workflows, venue inventory control, payment review processes, and administrative accountability while preserving historical records for reporting and audit purposes.

---

# A. Final Entity Relationship Diagram

```mermaid
erDiagram

    USERS ||--o{ ORDERS : creates
    USERS ||--o{ FAVORITES : saves
    USERS ||--o{ SEAT_HOLDS : owns

    STAFF_ACCOUNTS ||--o{ STAFF_VENUE_ASSIGNMENTS : assigned_to
    STAFF_ACCOUNTS ||--o{ EVENTS : creates
    STAFF_ACCOUNTS ||--o{ AUDIT_LOGS : performs
    STAFF_ACCOUNTS ||--o{ ARCHIVE_RECORDS : archives

    VENUES ||--o{ STAFF_VENUE_ASSIGNMENTS : grants_access
    VENUES ||--o{ VENUE_ALIASES : contains
    VENUES ||--o{ VENUE_LAYOUTS : contains
    VENUES ||--o{ EVENTS : hosts

    VENUE_LAYOUTS ||--o{ VENUE_TIERS : contains
    VENUE_LAYOUTS ||--o{ VENUE_SECTIONS : contains
    VENUE_LAYOUTS ||--o{ VENUE_NON_SEAT_AREAS : contains

    VENUE_TIERS ||--o{ VENUE_SECTIONS : groups
    VENUE_TIERS ||--o{ EVENT_TIER_SETTINGS : configures
    VENUE_TIERS ||--o{ TIER_BLOCKS : blocks

    VENUE_SECTIONS ||--o{ SEATS : contains
    VENUE_SECTIONS ||--o{ VENUE_SECTION_POINTS : defines
    VENUE_SECTIONS ||--o{ EVENT_SECTION_INVENTORY : tracks

    VENUE_NON_SEAT_AREAS ||--o{ VENUE_NON_SEAT_AREA_POINTS : defines

    EVENTS ||--o{ EVENT_PERFORMANCES : schedules
    EVENTS ||--o{ EVENT_TIER_SETTINGS : configures
    EVENTS ||--o{ EVENT_SECTION_INVENTORY : manages
    EVENTS ||--o{ SEAT_HOLDS : reserves
    EVENTS ||--o{ ORDERS : receives
    EVENTS ||--o{ SEAT_BLOCKS : blocks
    EVENTS ||--o{ TIER_BLOCKS : blocks
    EVENTS ||--o{ FAVORITES : bookmarked

    EVENT_PERFORMANCES ||--o{ EVENT_SECTION_INVENTORY : tracks
    EVENT_PERFORMANCES ||--o{ SEAT_HOLDS : reserves
    EVENT_PERFORMANCES ||--o{ ORDERS : books
    EVENT_PERFORMANCES ||--o{ SEAT_BLOCKS : blocks

    SEAT_HOLDS ||--o{ SEAT_HOLD_ITEMS : contains

    ORDERS ||--o{ ORDER_SEATS : contains
    ORDERS ||--o{ PAYMENTS : receives
    ORDERS ||--o{ TICKETS : generates
    ORDERS ||--o{ VOUCHERS : issues

    PAYMENTS ||--o{ PAYMENT_PROOFS : stores

    TICKETS ||--o{ CHECKIN_LOGS : validates
    TICKETS ||--o{ TICKET_PRINT_LOGS : prints

    SEATS ||--o{ SEAT_HOLD_ITEMS : reserved
    SEATS ||--o{ ORDER_SEATS : purchased
    SEATS ||--o{ TICKETS : assigned
    SEATS ||--o{ SEAT_BLOCKS : blocked
```

---

# B. Final Database Schema

## Table: users

Purpose:

Stores all customer accounts used for ticket purchases, seat reservations, favorites, and event participation.

Primary Key:

* id

Important Fields:

* name
* email
* password_hash
* status
* created_at
* updated_at

---

## Table: staff_accounts

Purpose:

Stores administrative, ticketing, venue, and operations personnel responsible for managing events, payments, inventories, ticket validation, and archive operations.

Primary Key:

* id

Important Fields:

* name
* email
* password_hash
* role
* status
* created_at
* updated_at

---

## Table: staff_venue_assignments

Purpose:

Defines venue-level permissions assigned to staff members.

Foreign Keys:

* staff_id → staff_accounts.id
* venue_id → venues.id

Key Permissions:

* create_events
* archive_events
* manage_tiers
* manage_seats
* review_payments
* print_tickets

---

## Table: venues

Purpose:

Stores all venues available for event hosting.

Primary Key:

* id

Important Fields:

* name
* slug
* status
* created_at
* updated_at

---

## Table: venue_aliases

Purpose:

Stores alternate names and searchable aliases associated with venues.

---

## Table: venue_layouts

Purpose:

Stores SVG-based venue seating maps and layout configurations.

Important Fields:

* layout_key
* variant
* category
* capacity
* svg_file
* map_type
* stage_label
* viewbox_x
* viewbox_y
* viewbox_width
* viewbox_height

---

## Table: venue_tiers

Purpose:

Defines venue pricing tiers such as VIP, Patron, Gold, Silver, and General Admission.

---

## Table: venue_sections

Purpose:

Defines seating sections within venue layouts.

---

## Table: venue_section_points

Purpose:

Stores polygon coordinates used for rendering section boundaries.

---

## Table: venue_non_seat_areas

Purpose:

Stores non-seating map regions such as stages, exits, aisles, and restricted zones.

---

## Table: venue_non_seat_area_points

Purpose:

Stores geometric coordinates for non-seat area rendering.

---

## Table: seats

Purpose:

Stores individual seats and seat metadata within venue sections.

---

## Table: events

Purpose:

Stores all published events available for ticket sales.

Important Fields:

* event_key
* title
* category
* artist
* company
* venue_id
* venue_layout_id
* base_price
* rating
* status

---

## Table: event_performances

Purpose:

Stores specific schedules and showtimes associated with events.

---

## Table: event_tier_settings

Purpose:

Stores pricing, capacity, and inventory settings per event tier.

---

## Table: event_section_inventory

Purpose:

Tracks seat inventory and availability at the section level.

---

## Table: seat_holds

Purpose:

Temporarily reserves seats during customer checkout sessions.

---

## Table: seat_hold_items

Purpose:

Stores seats included in a reservation hold.

---

## Table: orders

Purpose:

Stores completed, pending, approved, rejected, and archived ticket orders.

---

## Table: order_seats

Purpose:

Stores seat-level purchase information and pricing snapshots.

---

## Table: payments

Purpose:

Stores payment transactions and payment review workflow records.

---

## Table: payment_proofs

Purpose:

Stores uploaded proof-of-payment documents.

---

## Table: tickets

Purpose:

Stores issued event tickets and validation credentials.

---

## Table: ticket_print_logs

Purpose:

Tracks ticket printing history and reprint activity.

---

## Table: checkin_logs

Purpose:

Tracks ticket scanning and venue entry validation.

---

## Table: seat_blocks

Purpose:

Stores administrator-created seat restrictions and seat-level inventory blocks.

---

## Table: tier_blocks

Purpose:

Stores administrator-created tier-level sales restrictions.

---

## Table: favorites

Purpose:

Stores user-bookmarked events.

---

## Table: vouchers

Purpose:

Stores issued voucher records associated with orders and ticketing operations.

---

## Table: audit_logs

Purpose:

Maintains a complete audit trail of administrative actions.

---

## Table: archive_records

Purpose:

Stores archive and restoration records for system entities.