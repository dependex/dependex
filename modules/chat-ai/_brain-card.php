<?php
/** NEURAL CORTEX 3D — card embeddabile (iframe di brain-3d.php?embed=1). Uso: require '_brain-card.php'; echo brain_card('380px'); */
declare(strict_types=1);
function brain_card(string $altezza = '380px', bool $link = true): string
{
    $st = ['neuroni' => 0, 'sinapsi' => 0]; try { $c = json_decode(demo_cfg('brain_stats', '') ?: 'null', true); if (is_array($c)) $st = $c + $st; } catch (Throwable $e) {}
    ob_start(); ?>
<div class="carta" id="brainCard" style="margin:0 0 11px;padding:12px 12px 10px">
  <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px">
    <div style="flex:1;min-width:170px"><div class="eti">Neural Cortex 3D — the living brain of the ecosystem</div>
      <div class="sub"><?= $st['neuroni'] ? number_format((int)$st['neuroni']) . ' neurons · ' . number_format((int)$st['sinapsi']) . ' synapses · ' : '' ?>neurons are members, nodes, network positions and tokens; synapses are structure and live transactions — it grows every day — drag to spin, pinch to zoom, tap a neuron to fire it</div></div>
    <?php if ($link): ?><a class="b mini" href="brain.php" style="flex:none">Open full screen ›</a><?php endif; ?>
  </div>
  <div style="position:relative;height:<?= e($altezza) ?>;max-height:70vw;min-height:240px;border-radius:12px;overflow:hidden;border:1px solid rgba(217,180,90,.25);background:#050505">
    <iframe src="brain-3d.php?embed=1" title="Neural Cortex 3D" loading="lazy" style="position:absolute;inset:0;width:100%;height:100%;border:0;display:block" allow="fullscreen"></iframe>
  </div>
</div>
<?php return (string)ob_get_clean();
}
