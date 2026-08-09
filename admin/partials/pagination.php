<?php
/**
 * Reusable Pagination Bar Partial
 * Expects $pagination (from get_pagination_data) and optional $query_params (array)
 */

if (!isset($pagination) || $pagination['total_pages'] <= 1) {
    // If only 1 page or no data, still show record summary if total_items > 0
    if (isset($pagination) && $pagination['total_items'] > 0): ?>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
            <span>Showing <span class="font-medium text-gray-700"><?= $pagination['start_item'] ?></span> to <span class="font-medium text-gray-700"><?= $pagination['end_item'] ?></span> of <span class="font-medium text-gray-700"><?= $pagination['total_items'] ?></span> results</span>
        </div>
    <?php endif;
    return;
}

$queryParams = $query_params ?? $_GET;
unset($queryParams['page']); // Remove current page key to re-append dynamically

function build_page_url($page_num, $params) {
    $params['page'] = $page_num;
    return '?' . http_build_query($params);
}

$page = $pagination['current_page'];
$total_pages = $pagination['total_pages'];

// Calculate page window (max 5 page buttons)
$range = 2;
$start_page = max(1, $page - $range);
$end_page   = min($total_pages, $page + $range);

if ($start_page > 1) {
    $end_page = min($total_pages, $end_page + (1 - $start_page));
    $start_page = 1;
}
if ($end_page < $total_pages) {
    $start_page = max(1, $start_page - ($end_page - $total_pages));
    $end_page = $total_pages;
}
?>

<div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-600">
    <div>
        Showing <span class="font-semibold text-gray-800"><?= $pagination['start_item'] ?></span> to <span class="font-semibold text-gray-800"><?= $pagination['end_item'] ?></span> of <span class="font-semibold text-gray-800"><?= $pagination['total_items'] ?></span> results
    </div>

    <div class="inline-flex items-center space-x-1 rounded-lg shadow-sm">
        <!-- PREVIOUS BUTTON -->
        <?php if ($pagination['has_prev']): ?>
            <a href="<?= build_page_url($page - 1, $queryParams) ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-l-md bg-white text-gray-700 hover:bg-gray-100 font-medium transition">
                <i class="fas fa-chevron-left mr-1"></i> Prev
            </a>
        <?php else: ?>
            <span class="inline-flex items-center px-3 py-1.5 border border-gray-200 rounded-l-md bg-gray-100 text-gray-400 font-medium cursor-not-allowed">
                <i class="fas fa-chevron-left mr-1"></i> Prev
            </span>
        <?php endif; ?>

        <!-- FIRST PAGE IF RANGE MOVED -->
        <?php if ($start_page > 1): ?>
            <a href="<?= build_page_url(1, $queryParams) ?>" class="px-3 py-1.5 border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 font-medium">1</a>
            <?php if ($start_page > 2): ?>
                <span class="px-2 py-1.5 border border-gray-200 bg-gray-50 text-gray-400">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <!-- PAGE NUMBERS -->
        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="px-3.5 py-1.5 border border-blue-600 bg-blue-600 text-white font-semibold shadow-sm"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= build_page_url($i, $queryParams) ?>" class="px-3.5 py-1.5 border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 font-medium transition"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <!-- LAST PAGE IF RANGE MOVED -->
        <?php if ($end_page < $total_pages): ?>
            <?php if ($end_page < $total_pages - 1): ?>
                <span class="px-2 py-1.5 border border-gray-200 bg-gray-50 text-gray-400">...</span>
            <?php endif; ?>
            <a href="<?= build_page_url($total_pages, $queryParams) ?>" class="px-3 py-1.5 border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 font-medium"><?= $total_pages ?></a>
        <?php endif; ?>

        <!-- NEXT BUTTON -->
        <?php if ($pagination['has_next']): ?>
            <a href="<?= build_page_url($page + 1, $queryParams) ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-r-md bg-white text-gray-700 hover:bg-gray-100 font-medium transition">
                Next <i class="fas fa-chevron-right ml-1"></i>
            </a>
        <?php else: ?>
            <span class="inline-flex items-center px-3 py-1.5 border border-gray-200 rounded-r-md bg-gray-100 text-gray-400 font-medium cursor-not-allowed">
                Next <i class="fas fa-chevron-right ml-1"></i>
            </span>
        <?php endif; ?>
    </div>
</div>
