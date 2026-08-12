import fs from 'fs/promises';
import path from 'path';
import { getIconsCSS } from '@iconify/utils';

const SOURCE_ROOTS = ['app', 'resources/menu'];
const SOURCE_EXTENSIONS = new Set(['.php', '.json']);
const FALLBACK_ICONS = [
  'alert-triangle',
  'arrow-left',
  'bell-off',
  'alert-circle',
  'circle-check',
  'circle-x',
  'device-floppy',
  'download',
  'edit',
  'eye',
  'filter',
  'help-circle',
  'key',
  'loader-2',
  'lock',
  'send',
  'trash',
  'user-check',
  'user-off',
  'x'
];
const ICON_ALIASES = {
  'trash-2': 'trash'
};

async function sourceFiles(directory) {
  const entries = await fs.readdir(directory, { withFileTypes: true });
  const files = await Promise.all(
    entries.map(entry => {
      const filePath = path.join(directory, entry.name);

      return entry.isDirectory() ? sourceFiles(filePath) : filePath;
    })
  );

  return files.flat().filter(filePath => SOURCE_EXTENSIONS.has(path.extname(filePath)));
}

async function usedIconNames() {
  const icons = new Set(FALLBACK_ICONS);
  const sourceGroups = await Promise.all(
    SOURCE_ROOTS.map(root => sourceFiles(path.resolve(process.cwd(), root)))
  );
  const patterns = [
    /\bti ti-([a-z0-9-]+)/g,
    /dashboard_icon_class\(\s*['"]([a-z0-9-]+)['"]\s*\)/g,
    /\bicon\s*=\s*['"]([a-z0-9-]+)['"]/g,
    /['"]icon['"]\s*=>\s*['"]([a-z0-9-]+)['"]/g
  ];

  for (const file of sourceGroups.flat()) {
    const content = await fs.readFile(file, 'utf8');

    for (const pattern of patterns) {
      for (const match of content.matchAll(pattern)) {
        const scannedIcon = match[1].replace(/-$/, '');
        const icon = ICON_ALIASES[scannedIcon] ?? scannedIcon;
        if (icon !== '') icons.add(icon);
      }
    }
  }

  return [...icons].sort();
}

export default function iconifyPlugin() {
  return {
    name: 'vite-iconify-plugin',
    apply: 'build',

    async buildStart() {
      console.log('🔨 Generating iconify CSS file...');

      const iconSetPath = path.resolve(process.cwd(), 'node_modules/@iconify/json/json/tabler.json');
      const iconSet = JSON.parse(await fs.readFile(iconSetPath, 'utf8'));
      const iconNames = await usedIconNames();
      const missingIcons = iconNames.filter(icon => !iconSet.icons[icon]);

      if (missingIcons.length > 0) {
        throw new Error(`Unknown Tabler icons: ${missingIcons.join(', ')}`);
      }

      const css = getIconsCSS(iconSet, iconNames, {
        iconSelector: '.ti-{name}',
        commonSelector: '.ti',
        format: 'expanded'
      });
      const outputPath = path.resolve(process.cwd(), 'resources/assets/vendor/fonts/iconify/iconify.css');

      await fs.mkdir(path.dirname(outputPath), { recursive: true });
      await fs.writeFile(outputPath, css, 'utf8');

      console.log(`✅ Generated ${iconNames.length} Tabler icons at: ${outputPath}`);
    }
  };
}
