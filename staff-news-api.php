<?php

require_once __DIR__ . '/includes/log.php';
require_once __DIR__ . '/includes/news-data.php';

header('Content-Type: application/json');
clicketRequireRoleJson(['admin', 'organizer'], 'Admin or organizer access required.');
$staff = currentStaff();
if (!$staff) { http_response_code(401); echo json_encode(['success' => false, 'message' => 'Staff sign-in required.']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success' => false, 'message' => 'POST required.']); exit; }

$title = trim((string) ($_POST['title'] ?? ''));
$category = trim((string) ($_POST['category'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));
$status = ucfirst(strtolower(trim((string) ($_POST['status'] ?? 'Draft'))));
$allowedCategories = ['For Fans', 'For Organizers', 'Platform Updates'];
if ($title === '' || $description === '' || !in_array($category, $allowedCategories, true) || !in_array($status, ['Draft', 'Published', 'Archived'], true)) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Add a headline, category, main description, and valid status.']); exit; }

$banner = $_FILES['banner'] ?? null;
$bannerFilename = '';
$hasBanner = is_array($banner) && (int) ($banner['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
if ($status === 'Published' && !$hasBanner) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'A 16:9 banner image is required before publishing.']); exit; }
if ($hasBanner) {
    if ((int) ($banner['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || empty($banner['tmp_name'])) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'The banner upload could not be read.']); exit; }
    $imageInfo = @getimagesize((string) $banner['tmp_name']);
    $allowedTypes = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png'];
    $imageType = (int) ($imageInfo[2] ?? 0);
    $width = (int) ($imageInfo[0] ?? 0); $height = (int) ($imageInfo[1] ?? 0);
    $ratio = $height > 0 ? $width / $height : 0;
    if (!isset($allowedTypes[$imageType]) || $width < 1200 || $height < 675 || $ratio < 1.70 || $ratio > 1.86) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Use a JPG or PNG banner at least 1200 × 675 px in a 16:9 rectangular format.']); exit; }
    $bannerDirectory = __DIR__ . '/storage/news-banners';
    if (!is_dir($bannerDirectory) && !mkdir($bannerDirectory, 0775, true) && !is_dir($bannerDirectory)) { http_response_code(500); echo json_encode(['success' => false, 'message' => 'Banner storage is unavailable.']); exit; }
    $bannerFilename = 'news-' . bin2hex(random_bytes(10)) . '.' . $allowedTypes[$imageType];
    if (!move_uploaded_file((string) $banner['tmp_name'], $bannerDirectory . '/' . $bannerFilename)) { http_response_code(500); echo json_encode(['success' => false, 'message' => 'Could not save the banner image.']); exit; }
}

$sectionHeaders = (array) ($_POST['section_header'] ?? []);
$sectionContents = (array) ($_POST['section_content'] ?? []);
$sections = [];
foreach ($sectionHeaders as $index => $header) {
    $header = trim((string) $header); $content = trim((string) ($sectionContents[$index] ?? ''));
    if ($header === '' && $content === '') continue;
    if ($header === '' || $content === '') { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Every section needs both a header and content.']); exit; }
    $sections[] = ['header' => $header, 'content' => $content];
}
if (!$sections) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Add at least one content section.']); exit; }

$authorStaffId = clicketDbStaffIdBySession($staff);
if (!$authorStaffId) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Staff account could not be verified.']); exit; }
try {
    $article = clicketCreateNewsArticle([
        'title' => $title,
        'category' => $category,
        'description' => $description,
        'banner' => $bannerFilename,
        'sections' => $sections,
        'status' => strtolower($status),
    ], $authorStaffId);
} catch (Throwable) {
    if ($bannerFilename !== '') {
        @unlink(__DIR__ . '/storage/news-banners/' . $bannerFilename);
    }
    http_response_code(500); echo json_encode(['success' => false, 'message' => 'Could not save the news article.']); exit;
}
echo json_encode(['success' => true, 'message' => $status === 'Published' ? 'Published to the public News page.' : 'News article saved outside the public News page.', 'article' => $article]);
