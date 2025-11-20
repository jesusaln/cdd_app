import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

function cleanVueFile(filePath) {
    let content = fs.readFileSync(filePath, 'utf8');
    const original = content;

    // Remover solo defineProps y defineEmits de imports
    // Caso 1: import { defineProps, defineEmits } from 'vue';
    content = content.replace(/import\s*\{\s*defineProps\s*,\s*defineEmits\s*\}\s*from\s*['"]vue['"]\s*;?\s*/g, '');
    content = content.replace(/import\s*\{\s*defineEmits\s*,\s*defineProps\s*\}\s*from\s*['"]vue['"]\s*;?\s*/g, '');

    // Caso 2: import { defineProps } from 'vue';
    content = content.replace(/import\s*\{\s*defineProps\s*\}\s*from\s*['"]vue['"]\s*;?\s*/g, '');
    content = content.replace(/import\s*\{\s*defineEmits\s*\}\s*from\s*['"]vue['"]\s*;?\s*/g, '');

    // Caso 3: import { ref, defineProps } from 'vue';  -> import { ref } from 'vue';
    content = content.replace(/import\s*\{\s*([^}]+),\s*defineProps\s*\}\s*from\s*(['"]vue['"])/g, 'import { $1 } from $2');
    content = content.replace(/import\s*\{\s*([^}]+),\s*defineEmits\s*\}\s*from\s*(['"]vue['"])/g, 'import { $1 } from $2');
    content = content.replace(/import\s*\{\s*defineProps\s*,\s*([^}]+)\}\s*from\s*(['"]vue['"])/g, 'import { $1 } from $2');
    content = content.replace(/import\s*\{\s*defineEmits\s*,\s*([^}]+)\}\s*from\s*(['"]vue['"])/g, 'import { $1 } from $2');

    // Limpiar comas dobles que pueden quedar
    content = content.replace(/import\s*\{\s*,\s*([^}]+)\}\s*from/g, 'import { $1 } from');
    content = content.replace(/import\s*\{\s*([^}]+)\s*,\s*,\s*([^}]+)\}\s*from/g, 'import { $1, $2 } from');
    content = content.replace(/,\s*,/g, ',');

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        return true;
    }
    return false;
}

function walkDirectory(dir) {
    const files = fs.readdirSync(dir);
    let updatedCount = 0;

    for (const file of files) {
        const filePath = path.join(dir, file);
        const stat = fs.statSync(filePath);

        if (stat.isDirectory()) {
            updatedCount += walkDirectory(filePath);
        } else if (file.endsWith('.vue')) {
            if (cleanVueFile(filePath)) {
                console.log(`✓ Updated: ${filePath}`);
                updatedCount++;
            }
        }
    }

    return updatedCount;
}

const resourcesJs = path.join(__dirname, 'resources', 'js');
console.log('Starting cleanup of Vue files...');
const count = walkDirectory(resourcesJs);
console.log(`\nCompleted! Updated ${count} files.`);
