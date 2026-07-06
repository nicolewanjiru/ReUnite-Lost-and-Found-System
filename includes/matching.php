<?php

function normalize_match_text($text)
{
    $text = strtolower($text ?? '');
    $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);
    $text = preg_replace('/\s+/', ' ', $text);

    return trim($text);
}

function tokenize_match_text($text)
{
    $stop_words = array(
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from',
        'in', 'is', 'it', 'of', 'on', 'or', 'the', 'this', 'to', 'was',
        'were', 'with', 'lost', 'found', 'item'
    );

    $tokens = explode(' ', normalize_match_text($text));
    $tokens = array_filter($tokens, function ($token) use ($stop_words) {
        return strlen($token) > 2 && !in_array($token, $stop_words);
    });

    return array_values(array_unique($tokens));
}

function calculate_match_score($lost_item, $found_item, $proof = '')
{
    $lost_text = implode(' ', array(
        $lost_item['item_name'] ?? '',
        $lost_item['description'] ?? '',
        $lost_item['category'] ?? '',
        $lost_item['location'] ?? ''
    ));

    $found_text = implode(' ', array(
        $found_item['item_name'] ?? '',
        $found_item['description'] ?? '',
        $found_item['category'] ?? '',
        $found_item['location'] ?? ''
    ));

    $lost_tokens = tokenize_match_text($lost_text);
    $found_tokens = tokenize_match_text($found_text);
    $proof_tokens = tokenize_match_text($proof);

    if (count($lost_tokens) === 0 || count($found_tokens) === 0) {
        return 0;
    }

    $shared = array_intersect($lost_tokens, $found_tokens);
    $coverage = count($shared) / max(count($lost_tokens), count($found_tokens));

    similar_text(normalize_match_text($lost_text), normalize_match_text($found_text), $similarity);

    $category_score = 0;
    if (($lost_item['category'] ?? '') === 'lost' && ($found_item['category'] ?? '') === 'found') {
        $category_score = 100;
    }

    $location_score = 0;
    if (!empty($lost_item['location']) && !empty($found_item['location'])) {
        similar_text(
            normalize_match_text($lost_item['location']),
            normalize_match_text($found_item['location']),
            $location_score
        );
    }

    $proof_overlap = 0;
    if (count($proof_tokens) > 0) {
        $proof_overlap = count(array_intersect($proof_tokens, $found_tokens)) / count($proof_tokens);
    }

    $score = ($coverage * 45) + ($similarity * 0.25) + ($category_score * 0.15) + ($location_score * 0.10) + ($proof_overlap * 5);

    return min(100, round($score, 2));
}

function find_best_lost_match($conn, $user_id, $found_item, $proof = '')
{
    $user_id = (int) $user_id;
    $sql = "SELECT * FROM items
            WHERE user_id='$user_id'
            AND category='lost'
            ORDER BY date_reported DESC";

    $result = mysqli_query($conn, $sql);
    $best = array('item' => null, 'score' => 0);

    if ($result) {
        while ($lost_item = mysqli_fetch_assoc($result)) {
            $score = calculate_match_score($lost_item, $found_item, $proof);

            if ($score > $best['score']) {
                $best = array('item' => $lost_item, 'score' => $score);
            }
        }
    }

    return $best;
}

?>
