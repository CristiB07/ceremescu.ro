/**
 * Simple HTML Editor
 * Replacement for TinyMCE with basic formatting features
 */

// Store the script path at load time
const SCRIPT_PATH = (function () {
    const scripts = document.getElementsByTagName('script');
    for (let i = scripts.length - 1; i >= 0; i--) {
        if (scripts[i].src.indexOf('simple-editor.js') !== -1) {
            const src = scripts[i].src;
            return src.substring(0, src.lastIndexOf('/'));
        }
    }
    return '';
})();

class SimpleEditor {
    constructor(selector) {
        // If selector is an element, wrap it in an array
        if (selector instanceof Element) {
            this.textareas = [selector];
        } else {
            this.textareas = document.querySelectorAll(selector);
        }
        this.editors = [];
        this.savedSelection = null;
        this.init();
        this.setupMessageListener();
    }

    init() {
        this.textareas.forEach((textarea, index) => {
            const editorWrapper = this.createEditor(textarea, index);
            textarea.style.display = 'none';
            textarea.parentNode.insertBefore(editorWrapper, textarea);
            this.editors.push({
                textarea: textarea,
                wrapper: editorWrapper,
                editor: editorWrapper.querySelector('.simple-editor-content'),
                toolbar: editorWrapper.querySelector('.simple-editor-toolbar')
            });
        });
    }

    setupMessageListener() {
        window.addEventListener('message', (event) => {
            if (event.data.type === 'updateHTML') {
                const editorId = event.data.editorId;
                const newHtml = event.data.html;

                if (this.editors[editorId]) {
                    this.editors[editorId].editor.innerHTML = newHtml;
                    this.editors[editorId].textarea.value = newHtml;
                }
            }
        });
    }

    applyHeading(editorElement, headingTag) {
        const selection = window.getSelection();
        if (selection.rangeCount === 0) return;

        const range = selection.getRangeAt(0);
        let selectedText = range.toString();

        // Find the block element containing the selection
        let blockElement = range.commonAncestorContainer;
        if (blockElement.nodeType === Node.TEXT_NODE) {
            blockElement = blockElement.parentElement;
        }

        // Find the nearest block element
        while (blockElement && blockElement !== editorElement && !this.isBlockElement(blockElement)) {
            blockElement = blockElement.parentElement;
        }

        if (blockElement && blockElement !== editorElement) {
            // If the block element is already a heading, change its tag
            if (/^H[1-6]$/.test(blockElement.tagName)) {
                const newHeading = document.createElement(headingTag);
                newHeading.innerHTML = blockElement.innerHTML;
                blockElement.parentNode.replaceChild(newHeading, blockElement);
                // Position cursor in the new heading
                const newRange = document.createRange();
                newRange.selectNodeContents(newHeading);
                newRange.collapse(false);
                selection.removeAllRanges();
                selection.addRange(newRange);
            } else {
                // Replace the block element with heading
                const heading = document.createElement(headingTag);
                heading.innerHTML = blockElement.innerHTML;
                blockElement.parentNode.replaceChild(heading, blockElement);
                // Position cursor in the new heading
                const newRange = document.createRange();
                newRange.selectNodeContents(heading);
                newRange.collapse(false);
                selection.removeAllRanges();
                selection.addRange(newRange);
            }
        } else {
            // No block element found, insert new heading
            if (selectedText.trim()) {
                const heading = document.createElement(headingTag);
                heading.innerHTML = selectedText;
                range.deleteContents();
                range.insertNode(heading);
                range.setStartAfter(heading);
                range.setEndAfter(heading);
                selection.removeAllRanges();
                selection.addRange(range);
            } else {
                const heading = document.createElement(headingTag);
                heading.innerHTML = '<br>';
                range.insertNode(heading);
                range.setStartAfter(heading);
                range.setEndAfter(heading);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        }

        // Update textarea
        const textarea = editorElement.closest('.simple-editor-wrapper').querySelector('textarea');
        if (textarea) {
            textarea.value = editorElement.innerHTML;
        }

        // Trigger auto-resize
        this.autoResizeEditor(editorElement);
    }

    isBlockElement(node) {
        const blockTags = ['P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'DIV', 'BLOCKQUOTE', 'PRE'];
        return node && node.nodeType === Node.ELEMENT_NODE && blockTags.includes(node.tagName);
    }

    autoResizeEditor(editorElement) {
        // Use setTimeout to ensure DOM is updated
        setTimeout(() => {
            // Reset height to auto to get the correct scrollHeight
            editorElement.style.height = 'auto';
            // Set height to scrollHeight to fit content
            const newHeight = editorElement.scrollHeight;
            editorElement.style.height = newHeight + 'px';
        }, 0);
    }

    decodeHtmlEntities(text) {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = text;
        return textarea.value;
    }

    saveSelection() {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            return {
                startContainer: range.startContainer,
                startOffset: range.startOffset,
                endContainer: range.endContainer,
                endOffset: range.endOffset
            };
        }
        return null;
    }

    restoreSelection(savedSelection) {
        if (savedSelection) {
            const selection = window.getSelection();
            const range = document.createRange();
            try {
                range.setStart(savedSelection.startContainer, savedSelection.startOffset);
                range.setEnd(savedSelection.endContainer, savedSelection.endOffset);
                selection.removeAllRanges();
                selection.addRange(range);
            } catch (e) {
                // In case the saved selection is no longer valid
                console.warn('Could not restore selection:', e);
            }
        }
    }

    handleTabIndent(editorElement) {
        const selection = window.getSelection();
        if (selection.rangeCount === 0) return;

        const range = selection.getRangeAt(0);
        if (range.collapsed) return; // No selection

        // Get all selected list items
        const selectedLis = this.getSelectedListItems(range, editorElement);
        if (selectedLis.length < 1) return;

        // Check if they are consecutive siblings
        const parent = selectedLis[0].parentElement;
        if (!parent || parent.tagName !== 'UL' && parent.tagName !== 'OL') return;

        const allLis = Array.from(parent.children).filter(child => child.tagName === 'LI');
        const indices = selectedLis.map(li => allLis.indexOf(li)).sort((a, b) => a - b);

        // Check if consecutive
        for (let i = 1; i < indices.length; i++) {
            if (indices[i] !== indices[i - 1] + 1) return;
        }

        // Get the position of the first selected LI in parent.children
        const childIndex = Array.from(parent.children).indexOf(selectedLis[0]);

        // Create new sublist
        const sublist = document.createElement(parent.tagName); // UL or OL
        selectedLis.forEach(li => {
            sublist.appendChild(li);
        });

        // Insert the sublist at the position of the first selected LI
        parent.insertBefore(sublist, parent.children[childIndex]);

        // Update textarea
        const textarea = editorElement.closest('.simple-editor-wrapper').querySelector('textarea');
        if (textarea) {
            textarea.value = editorElement.innerHTML;
        }

        // Trigger auto-resize
        this.autoResizeEditor(editorElement);
    }

    getSelectedListItems(range, editorElement) {
        const lis = [];
        const treeWalker = document.createTreeWalker(
            editorElement,
            NodeFilter.SHOW_ELEMENT,
            {
                acceptNode: function (node) {
                    return node.tagName === 'LI' ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_SKIP;
                }
            }
        );

        let node;
        while (node = treeWalker.nextNode()) {
            if (range.intersectsNode(node)) {
                lis.push(node);
            }
        }

        return lis;
    }

    createEditor(textarea, index) {
        const wrapper = document.createElement('div');
        wrapper.className = 'simple-editor-wrapper';
        wrapper.dataset.editorId = index;

        const toolbar = this.createToolbar(index);
        const editorContent = document.createElement('div');
        editorContent.className = 'simple-editor-content';
        editorContent.contentEditable = 'true';
        editorContent.dataset.editorId = index;
        editorContent.innerHTML = this.decodeHtmlEntities(textarea.value) || '';

        // Initial auto-resize
        this.autoResizeEditor(editorContent);

        // Prevent headings from becoming paragraphs when pressing Enter
        editorContent.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    let node = range.commonAncestorContainer;
                    if (node.nodeType === Node.TEXT_NODE) {
                        node = node.parentElement;
                    }

                    // Check if we're in a block element (not just headings)
                    while (node && node !== editorContent) {
                        if (node.tagName && (this.isBlockElement(node) || /^H[1-6]$/.test(node.tagName))) {
                            e.preventDefault();
                            // Insert line break instead of new paragraph
                            document.execCommand('insertHTML', false, '<br>');
                            return;
                        }
                        node = node.parentElement;
                    }
                }
            } else if (e.key === 'Tab') {
                e.preventDefault();
                this.handleTabIndent(editorContent);
            }
        });

        // Sync content back to textarea
        editorContent.addEventListener('input', () => {
            // Prevent headings from being converted to paragraphs
            const headings = editorContent.querySelectorAll('h1, h2, h3, h4, h5, h6');
            headings.forEach(heading => {
                if (heading.parentElement && heading.parentElement.tagName === 'P') {
                    // Move heading out of paragraph
                    heading.parentElement.parentElement.insertBefore(heading, heading.parentElement);
                    // Remove empty paragraph
                    if (!heading.parentElement.textContent.trim() && heading.parentElement.children.length === 0) {
                        heading.parentElement.remove();
                    }
                }
            });

            // Prevent list items from being wrapped in paragraphs
            const listItems = editorContent.querySelectorAll('li');
            listItems.forEach(li => {
                if (li.parentElement && li.parentElement.tagName === 'P') {
                    // Move li out of paragraph
                    li.parentElement.parentElement.insertBefore(li, li.parentElement);
                    // Remove empty paragraph
                    if (!li.parentElement.textContent.trim() && li.parentElement.children.length === 0) {
                        li.parentElement.remove();
                    }
                }
            });

            // Prevent lists from being wrapped in paragraphs
            const lists = editorContent.querySelectorAll('ol, ul');
            lists.forEach(list => {
                if (list.parentElement && list.parentElement.tagName === 'P') {
                    // Move list out of paragraph
                    list.parentElement.parentElement.insertBefore(list, list.parentElement);
                    // Remove empty paragraph
                    if (!list.parentElement.textContent.trim() && list.parentElement.children.length === 0) {
                        list.parentElement.remove();
                    }
                }
            });

            // Remove empty paragraphs
            const emptyParagraphs = editorContent.querySelectorAll('p:empty');
            emptyParagraphs.forEach(p => p.remove());

            textarea.value = editorContent.innerHTML;
            // Auto-resize editor height
            this.autoResizeEditor(editorContent);
        });

        // Use MutationObserver to merge lists in real-time
        const observer = new MutationObserver(() => {
            // Merge consecutive lists of the same type
            const allLists = editorContent.querySelectorAll('ol, ul');
            for (let i = 0; i < allLists.length - 1; i++) {
                const current = allLists[i];
                const next = allLists[i + 1];
                if (current.tagName === next.tagName && current.nextSibling === next) {
                    // Move all li from next to current
                    while (next.firstChild) {
                        current.appendChild(next.firstChild);
                    }
                    next.remove();
                    // Adjust loop since we removed an element
                    i--;
                }
            }
        });
        observer.observe(editorContent, { childList: true, subtree: true });

        // Allow cursor placement anywhere on click
        editorContent.addEventListener('click', (e) => {
            // Use caretRangeFromPoint to place cursor at click position
            if (document.caretRangeFromPoint) {
                const range = document.caretRangeFromPoint(e.clientX, e.clientY);
                if (range) {
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);
                    e.preventDefault(); // Prevent default browser behavior
                }
            }
        });

        // Also handle keyup for better responsiveness
        editorContent.addEventListener('keyup', () => {
            this.autoResizeEditor(editorContent);
        });

        // Also resize on paste
        editorContent.addEventListener('paste', () => {
            setTimeout(() => {
                this.autoResizeEditor(editorContent);
            }, 10);
        });

        // Handle image clicks for editing properties
        editorContent.addEventListener('click', (e) => {
            if (e.target.tagName === 'IMG') {
                e.preventDefault();
                this.editImageProperties(e.target, index);
            }
        });

        wrapper.appendChild(toolbar);
        wrapper.appendChild(editorContent);

        return wrapper;
    }

    createToolbar(editorId) {
        const toolbar = document.createElement('div');
        toolbar.className = 'simple-editor-toolbar';

        // Format buttons group
        const formatButtons = [
            { title: 'Paragraph', icon: '<i class="fas fa-paragraph"></i>', command: 'formatBlock', value: 'P' },
            { title: 'Blockquote', icon: '<i class="fas fa-quote-right"></i>', command: 'formatBlock', value: 'BLOCKQUOTE' },
            { title: 'Code Block', icon: '<i class="fas fa-code"></i>', command: 'formatBlock', value: 'PRE' }
        ];

        formatButtons.forEach(btn => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'toolbar-btn';
            button.title = btn.title;
            button.innerHTML = btn.icon;

            button.addEventListener('click', (e) => {
                e.preventDefault();
                button.blur();
                const editor = this.editors[editorId];
                if (editor && editor.editor) {
                    editor.editor.focus();
                }
                document.execCommand(btn.command, false, btn.value || null);
            });

            toolbar.appendChild(button);
        });

        // Create headings dropdown with wrapper
        const headingsWrapper = document.createElement('div');
        headingsWrapper.className = 'toolbar-dropdown-wrapper';

        const headingsBtn = document.createElement('button');
        headingsBtn.type = 'button';
        headingsBtn.className = 'toolbar-btn toolbar-dropdown-btn';
        headingsBtn.title = 'Headings';
        headingsBtn.innerHTML = '<i class="fas fa-heading"></i>';

        const headingsMenu = document.createElement('div');
        headingsMenu.className = 'toolbar-dropdown-menu';
        headingsMenu.innerHTML = `
            <div class="dropdown-item" data-value="H1">Heading 1</div>
            <div class="dropdown-item" data-value="H2">Heading 2</div>
            <div class="dropdown-item" data-value="H3">Heading 3</div>
            <div class="dropdown-item" data-value="H4">Heading 4</div>
            <div class="dropdown-item" data-value="H5">Heading 5</div>
        `;

        headingsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            // Save current selection before opening dropdown
            this.savedSelection = this.saveSelection();
            // Close other dropdowns
            document.querySelectorAll('.toolbar-dropdown-menu.show').forEach(menu => {
                if (menu !== headingsMenu) menu.classList.remove('show');
            });
            headingsMenu.classList.toggle('show');
        });

        headingsMenu.addEventListener('click', (e) => {
            if (e.target.classList.contains('dropdown-item')) {
                e.preventDefault();
                // Restore selection before applying heading
                this.restoreSelection(this.savedSelection);
                const value = e.target.dataset.value;
                const editor = this.editors[editorId];
                if (editor && editor.editor) {
                    this.applyHeading(editor.editor, value);
                }
                headingsMenu.classList.remove('show');
                // Clear saved selection
                this.savedSelection = null;
            }
        });

        headingsWrapper.appendChild(headingsBtn);
        headingsWrapper.appendChild(headingsMenu);
        toolbar.appendChild(headingsWrapper);

        // Add separator after format buttons
        const separator1 = document.createElement('span');
        separator1.className = 'toolbar-separator';
        separator1.innerHTML = '|';
        toolbar.appendChild(separator1);

        const buttons = [
            { title: 'Bold', icon: '<i class="fas fa-bold"></i>', command: 'bold' },
            { title: 'Italic', icon: '<i class="fas fa-italic"></i>', command: 'italic' },
            { title: 'Underline', icon: '<i class="fas fa-underline"></i>', command: 'underline' },
            { title: 'Strikethrough', icon: '<i class="fas fa-strikethrough"></i>', command: 'strikethrough' },
            { title: 'Superscript', icon: '<i class="fas fa-superscript"></i>', command: 'superscript' },
            { title: 'Subscript', icon: '<i class="fas fa-subscript"></i>', command: 'subscript' },
            { type: 'separator' },
            { title: 'Align Left', icon: '<i class="fas fa-align-left"></i>', command: 'justifyLeft' },
            { title: 'Align Center', icon: '<i class="fas fa-align-center"></i>', command: 'justifyCenter' },
            { title: 'Align Right', icon: '<i class="fas fa-align-right"></i>', command: 'justifyRight' },
            { title: 'Justify', icon: '<i class="fas fa-align-justify"></i>', command: 'justifyFull' },
            { type: 'separator' },
            { title: 'Ordered List', icon: '<i class="fas fa-list-ol"></i>', command: 'insertOrderedList' },
            { title: 'Unordered List', icon: '<i class="fas fa-list-ul"></i>', command: 'insertUnorderedList' },
            { type: 'separator' },
            { title: 'Insert Table', icon: '<i class="fas fa-table"></i>', action: 'insertTable', editorId: editorId, isTableBtn: true },
            { title: 'Insert Link', icon: '<i class="fas fa-link"></i>', action: 'insertLink', editorId: editorId },
            { title: 'Insert Image', icon: '<i class="fas fa-image"></i>', action: 'insertImage', editorId: editorId },
            { title: 'Page Break', icon: '<i class="fas fa-minus"></i>', action: 'insertPageBreak', editorId: editorId },
            { type: 'separator' },
            { title: 'Paste as Plain Text', icon: '<i class="fas fa-clipboard"></i>', action: 'pasteAsPlainText', editorId: editorId },
            { title: 'View HTML', icon: '<i class="fas fa-file-code"></i>', action: 'viewHTML', editorId: editorId },
            { title: 'Preview', icon: '<i class="fas fa-eye"></i>', action: 'preview', editorId: editorId },
            { title: 'Fullscreen', icon: '<i class="fas fa-expand"></i>', action: 'toggleFullscreen', editorId: editorId },
            { title: 'Clear Formatting', icon: '<i class="fas fa-eraser"></i>', command: 'removeFormat' }
        ];

        buttons.forEach(btn => {
            if (btn.type === 'separator') {
                const separator = document.createElement('span');
                separator.className = 'toolbar-separator';
                separator.innerHTML = '|';
                toolbar.appendChild(separator);
            } else {
                // Special handling for table button with grid picker
                if (btn.isTableBtn) {
                    const tableWrapper = document.createElement('div');
                    tableWrapper.className = 'toolbar-dropdown-wrapper table-picker-wrapper';

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'toolbar-btn';
                    button.title = btn.title;
                    button.innerHTML = btn.icon;

                    const tablePicker = this.createTablePicker(editorId);

                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        // Close other dropdowns
                        document.querySelectorAll('.toolbar-dropdown-menu.show, .table-grid-picker.show').forEach(menu => {
                            if (menu !== tablePicker) menu.classList.remove('show');
                        });
                        tablePicker.classList.toggle('show');
                    });

                    tableWrapper.appendChild(button);
                    tableWrapper.appendChild(tablePicker);
                    toolbar.appendChild(tableWrapper);
                } else {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'toolbar-btn';
                    button.title = btn.title;
                    button.innerHTML = btn.icon;

                    if (btn.command) {
                        button.addEventListener('click', (e) => {
                            e.preventDefault();
                            button.blur(); // Remove focus from button
                            const editor = this.editors[editorId];
                            if (editor && editor.editor) {
                                editor.editor.focus();
                            }
                            document.execCommand(btn.command, false, btn.value || null);
                        });
                    } else if (btn.action) {
                        button.addEventListener('click', (e) => {
                            e.preventDefault();
                            button.blur(); // Remove focus from button
                            const editor = this.editors[editorId];
                            if (editor && editor.editor) {
                                editor.editor.focus();
                            }
                            this[btn.action](btn.editorId);
                        });
                    }

                    toolbar.appendChild(button);
                }
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.toolbar-dropdown-wrapper')) {
                document.querySelectorAll('.toolbar-dropdown-menu.show, .table-grid-picker.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        return toolbar;
    }

    createTablePicker(editorId) {
        const picker = document.createElement('div');
        picker.className = 'table-grid-picker';

        const label = document.createElement('div');
        label.className = 'table-picker-label';
        label.textContent = '1 x 1';

        const grid = document.createElement('div');
        grid.className = 'table-picker-grid';

        // Create 10x10 grid
        const maxRows = 10;
        const maxCols = 10;

        for (let row = 0; row < maxRows; row++) {
            for (let col = 0; col < maxCols; col++) {
                const cell = document.createElement('div');
                cell.className = 'table-picker-cell';
                cell.dataset.row = row;
                cell.dataset.col = col;

                cell.addEventListener('mouseenter', () => {
                    // Highlight cells up to this one
                    const r = parseInt(cell.dataset.row);
                    const c = parseInt(cell.dataset.col);

                    grid.querySelectorAll('.table-picker-cell').forEach(c => {
                        c.classList.remove('hover');
                    });

                    for (let i = 0; i <= r; i++) {
                        for (let j = 0; j <= c; j++) {
                            const targetCell = grid.querySelector(`[data-row="${i}"][data-col="${j}"]`);
                            if (targetCell) {
                                targetCell.classList.add('hover');
                            }
                        }
                    }

                    label.textContent = `${r + 1} x ${c + 1}`;
                });

                cell.addEventListener('click', (e) => {
                    e.preventDefault();
                    const rows = parseInt(cell.dataset.row) + 1;
                    const cols = parseInt(cell.dataset.col) + 1;
                    this.insertTableWithSize(editorId, rows, cols);
                    picker.classList.remove('show');

                    // Reset grid
                    grid.querySelectorAll('.table-picker-cell').forEach(c => {
                        c.classList.remove('hover');
                    });
                    label.textContent = '1 x 1';
                });

                grid.appendChild(cell);
            }
        }

        grid.addEventListener('mouseleave', () => {
            grid.querySelectorAll('.table-picker-cell').forEach(c => {
                c.classList.remove('hover');
            });
            label.textContent = '1 x 1';
        });

        picker.appendChild(label);
        picker.appendChild(grid);

        return picker;
    }

    insertTable(editorId) {
        // This method is kept for backward compatibility but not directly used
        // The grid picker handles table insertion
    }

    insertTableWithSize(editorId, rows, cols) {
        const editor = this.editors[editorId];
        if (!editor || !editor.editor) return;

        editor.editor.focus();

        // Create table HTML
        let tableHTML = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; margin: 10px 0;">';
        tableHTML += '<tbody>';

        for (let i = 0; i < rows; i++) {
            tableHTML += '<tr>';
            for (let j = 0; j < cols; j++) {
                tableHTML += '<td style="border: 1px solid #ddd; padding: 8px; min-width: 50px;">&nbsp;</td>';
            }
            tableHTML += '</tr>';
        }

        tableHTML += '</tbody></table>';
        tableHTML += '<p><br></p>'; // Add paragraph after table for easier editing

        // Insert the table
        document.execCommand('insertHTML', false, tableHTML);
    }

    insertLink(editorId) {
        const url = prompt('Enter URL:');
        if (url) {
            document.execCommand('createLink', false, url);
        }
    }

    insertImage(editorId) {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                this.uploadImage(file, editorId);
            }
        };
        input.click();
    }

    uploadImage(file, editorId) {
        const formData = new FormData();
        formData.append('file', file);

        // Get directory from textarea's data attribute or default to 'blog'
        const directory = this.editors[editorId].textarea.dataset.uploadDir || 'blog';
        formData.append('directory', directory);

        // Use absolute URL to PHP server
        const uploadUrl = 'http://localhost:8000/common/postAcceptor.php';

        fetch(uploadUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.error || 'Upload failed');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert('Error uploading image: ' + data.error);
                    return;
                }
                if (data.location) {
                    // Use the full path returned by server
                    const imgPath = data.location.replace(`../img/${directory}/`, '');
                    const img = `<img src="../img/${directory}/${imgPath}" alt="${file.name}" style="max-width: 100%; cursor: pointer;" class="editor-image">`;
                    this.editors[editorId].editor.focus();
                    document.execCommand('insertHTML', false, img);
                }
            })
            .catch(error => {
                alert('Error uploading image: ' + error.message);
            });
    }

    editImageProperties(img, editorId) {
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'image-props-overlay';
        overlay.innerHTML = `
            <div class="image-props-modal">
                <h3>Image Properties</h3>
                <div class="image-props-preview">
                    <img src="${img.src}" alt="Preview" style="max-width: 300px; max-height: 200px;">
                </div>
                <div class="image-props-form">
                    <div class="form-group">
                        <label>Alt Text:</label>
                        <input type="text" id="img-alt" value="${img.alt || ''}" placeholder="Image description">
                    </div>
                    <div class="form-group">
                        <label>Image URL:</label>
                        <input type="text" id="img-src" value="${img.src || ''}" placeholder="Image URL">
                    </div>
                    <div class="form-group">
                        <label>Width:</label>
                        <input type="text" id="img-width" value="${img.style.width || 'auto'}" placeholder="e.g., 100%, 500px, auto">
                    </div>
                    <div class="form-group">
                        <label>Alignment / Text Flow:</label>
                        <select id="img-align">
                            <option value="none">None (Block)</option>
                            <option value="left">Float Left (text wraps right)</option>
                            <option value="right">Float Right (text wraps left)</option>
                            <option value="center">Center (Block)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Padding:</label>
                        <input type="text" id="img-padding" value="${img.style.padding || '0'}" placeholder="e.g., 10px, 5px 10px">
                    </div>
                    <div class="form-group">
                        <label>Margin:</label>
                        <input type="text" id="img-margin" value="${img.style.margin || '0'}" placeholder="e.g., 10px, 5px 10px">
                    </div>
                    <div class="form-group">
                        <label>Border:</label>
                        <input type="text" id="img-border" value="${img.style.border || 'none'}" placeholder="e.g., 1px solid #000">
                    </div>
                    <div class="form-group">
                        <label>Border Radius:</label>
                        <input type="text" id="img-border-radius" value="${img.style.borderRadius || '0'}" placeholder="e.g., 5px, 50%">
                    </div>
                </div>
                <div class="image-props-buttons">
                    <button class="btn-apply">Apply</button>
                    <button class="btn-remove">Remove Image</button>
                    <button class="btn-cancel">Cancel</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // Get current alignment
        const currentFloat = img.style.float || 'none';
        const currentDisplay = img.style.display || 'block';
        const currentMargin = img.style.margin || '0';

        let alignValue = 'none';
        if (currentFloat === 'left') alignValue = 'left';
        else if (currentFloat === 'right') alignValue = 'right';
        else if (currentDisplay === 'block' && (currentMargin.includes('auto') || img.style.marginLeft === 'auto')) alignValue = 'center';

        overlay.querySelector('#img-align').value = alignValue;

        // Apply button
        overlay.querySelector('.btn-apply').addEventListener('click', () => {
            img.alt = overlay.querySelector('#img-alt').value;
            img.src = overlay.querySelector('#img-src').value;
            img.style.width = overlay.querySelector('#img-width').value;
            img.style.padding = overlay.querySelector('#img-padding').value;
            img.style.margin = overlay.querySelector('#img-margin').value;
            img.style.border = overlay.querySelector('#img-border').value;
            img.style.borderRadius = overlay.querySelector('#img-border-radius').value;

            const align = overlay.querySelector('#img-align').value;
            // Reset alignment styles
            img.style.float = 'none';
            img.style.display = 'inline';
            img.style.marginLeft = '';
            img.style.marginRight = '';

            switch (align) {
                case 'left':
                    img.style.float = 'left';
                    img.style.marginRight = '15px';
                    img.style.marginBottom = '10px';
                    break;
                case 'right':
                    img.style.float = 'right';
                    img.style.marginLeft = '15px';
                    img.style.marginBottom = '10px';
                    break;
                case 'center':
                    img.style.display = 'block';
                    img.style.marginLeft = 'auto';
                    img.style.marginRight = 'auto';
                    break;
            }

            img.style.cursor = 'pointer';
            this.editors[editorId].textarea.value = this.editors[editorId].editor.innerHTML;
            document.body.removeChild(overlay);
        });

        // Remove button
        overlay.querySelector('.btn-remove').addEventListener('click', () => {
            if (confirm('Are you sure you want to remove this image?')) {
                img.remove();
                this.editors[editorId].textarea.value = this.editors[editorId].editor.innerHTML;
                document.body.removeChild(overlay);
            }
        });

        // Cancel button
        overlay.querySelector('.btn-cancel').addEventListener('click', () => {
            document.body.removeChild(overlay);
        });

        // Close on overlay click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                document.body.removeChild(overlay);
            }
        });
    }

    insertPageBreak(editorId) {
        const pageBreak = '<hr class="page-break" style="border: 2px dashed #ccc; page-break-after: always; margin: 20px 0;">';
        this.editors[editorId].editor.focus();
        document.execCommand('insertHTML', false, pageBreak);
    }

    pasteAsPlainText(editorId) {
        const editor = this.editors[editorId];

        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'image-props-overlay';
        overlay.innerHTML = `
            <div class="image-props-modal" style="max-width: 600px;">
                <h3>Paste as Plain Text</h3>
                <div class="image-props-form">
                    <div class="form-group">
                        <label>Paste your content here (all formatting will be removed):</label>
                        <textarea id="paste-content" style="width: 100%; min-height: 200px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif;" placeholder="Paste your text here..."></textarea>
                    </div>
                </div>
                <div class="image-props-buttons">
                    <button class="btn-apply">Insert Text</button>
                    <button class="btn-cancel">Cancel</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const textarea = overlay.querySelector('#paste-content');
        textarea.focus();

        // Apply button
        overlay.querySelector('.btn-apply').addEventListener('click', () => {
            const plainText = textarea.value;
            if (plainText.trim()) {
                // Convert line breaks to <br> tags and escape HTML
                const htmlContent = plainText
                    .split('\n')
                    .map(line => line.trim())
                    .filter(line => line.length > 0)
                    .map(line => `<p>${line.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>`)
                    .join('');

                editor.editor.focus();
                document.execCommand('insertHTML', false, htmlContent);
                this.editors[editorId].textarea.value = editor.editor.innerHTML;
            }
            document.body.removeChild(overlay);
        });

        // Cancel button
        overlay.querySelector('.btn-cancel').addEventListener('click', () => {
            document.body.removeChild(overlay);
        });

        // Close on overlay click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                document.body.removeChild(overlay);
            }
        });
    }

    viewHTML(editorId) {
        const editor = this.editors[editorId];
        const content = editor.editor.innerHTML;

        const htmlWindow = window.open('', 'HTML Code', 'width=800,height=600,resizable=yes,scrollbars=yes');
        htmlWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>HTML Code</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        padding: 20px; 
                        margin: 0;
                    }
                    textarea {
                        width: 100%;
                        min-height: 500px;
                        font-family: 'Courier New', monospace;
                        padding: 15px;
                        border: 1px solid #ddd;
                        resize: vertical;
                        font-size: 14px;
                        line-height: 1.5;
                        box-sizing: border-box;
                    }
                    .buttons {
                        margin-bottom: 15px;
                    }
                    button {
                        padding: 10px 20px;
                        margin-right: 10px;
                        background: #0066cc;
                        color: white;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 14px;
                    }
                    button:hover {
                        background: #0052a3;
                    }
                    button.success {
                        background: #28a745;
                    }
                    button.success:hover {
                        background: #218838;
                    }
                    .info {
                        background: #f0f8ff;
                        padding: 10px;
                        border-left: 4px solid #0066cc;
                        margin-bottom: 15px;
                    }
                </style>
            </head>
            <body>
                <div class="info">
                    <strong>Note:</strong> You can edit the HTML code below. Click "Apply Changes" to update the editor.
                </div>
                <div class="buttons">
                    <button class="success" onclick="applyChanges()">Apply Changes</button>
                    <button onclick="selectAll()">Select All</button>
                    <button onclick="copyToClipboard()">Copy to Clipboard</button>
                    <button onclick="window.close()">Close</button>
                </div>
                <textarea id="htmlCode">${content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
                <script>
                    function selectAll() {
                        document.getElementById('htmlCode').select();
                    }
                    function copyToClipboard() {
                        const textarea = document.getElementById('htmlCode');
                        textarea.select();
                        document.execCommand('copy');
                        alert('HTML code copied to clipboard!');
                    }
                    function applyChanges() {
                        const textarea = document.getElementById('htmlCode');
                        const newHtml = textarea.value;
                        
                        // Send message to opener window
                        if (window.opener && !window.opener.closed) {
                            window.opener.postMessage({
                                type: 'updateHTML',
                                editorId: ${editorId},
                                html: newHtml
                            }, '*');
                            alert('Changes applied successfully!');
                            window.close();
                        } else {
                            alert('Parent window is closed. Cannot apply changes.');
                        }
                    }
                </script>
            </body>
            </html>
        `);
        htmlWindow.document.close();
    }

    preview(editorId) {
        const editor = this.editors[editorId];
        const content = editor.editor.innerHTML;

        const previewWindow = window.open('', 'Preview', 'width=800,height=600');
        previewWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Preview</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        padding: 20px; 
                        max-width: 900px; 
                        margin: 0 auto;
                    }
                    img { max-width: 100%; height: auto; }
                    pre { 
                        background: #f4f4f4; 
                        padding: 15px; 
                        border-radius: 5px; 
                        overflow-x: auto;
                    }
                    .page-break {
                        border: 2px dashed #ccc;
                        page-break-after: always;
                        margin: 20px 0;
                    }
                </style>
            </head>
            <body>
                ${content}
            </body>
            </html>
        `);
        previewWindow.document.close();
    }

    toggleFullscreen(editorId) {
        const editor = this.editors[editorId];
        if (editor && editor.wrapper) {
            const wrapper = editor.wrapper;
            const isFullscreen = wrapper.classList.contains('fullscreen');

            if (isFullscreen) {
                // Exit fullscreen
                wrapper.classList.remove('fullscreen');
                document.body.style.overflow = '';
                // Update button icon
                const fullscreenBtn = wrapper.querySelector('[title="Fullscreen"] i');
                if (fullscreenBtn) {
                    fullscreenBtn.className = 'fas fa-expand';
                }
            } else {
                // Enter fullscreen
                wrapper.classList.add('fullscreen');
                document.body.style.overflow = 'hidden';
                // Update button icon
                const fullscreenBtn = wrapper.querySelector('[title="Fullscreen"] i');
                if (fullscreenBtn) {
                    fullscreenBtn.className = 'fas fa-compress';
                }
            }

            // Focus back to editor
            setTimeout(() => {
                editor.editor.focus();
            }, 100);
        }
    }
}

// Initialize editors when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    // Function to initialize editors
    function initEditors() {
        const selectors = [
            '.simple-html-editor',
            '#simple-html-editor',
            'textarea.simple-editor',
            'textarea.simple-html-editor'
        ];

        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(textarea => {
                if (!textarea.hasAttribute('data-editor-initialized') && !textarea.closest('.simple-editor-wrapper')) {
                    textarea.setAttribute('data-editor-initialized', 'true');
                    new SimpleEditor(textarea);
                }
            });
        });
    }
    initEditors();

    // Also initialize after a short delay in case elements are added dynamically
    setTimeout(initEditors, 100);

    // Watch for dynamically added elements
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) { // Element node
                    const textareas = node.querySelectorAll ? node.querySelectorAll('textarea.simple-html-editor, textarea.simple-editor') : [];
                    Array.from(textareas).forEach(textarea => {
                        if (!textarea.hasAttribute('data-editor-initialized') && !textarea.closest('.simple-editor-wrapper')) {
                            textarea.setAttribute('data-editor-initialized', 'true');
                            new SimpleEditor(textarea);
                        }
                    });
                    // Also check if the node itself is a textarea
                    if (node.tagName === 'TEXTAREA' &&
                        (node.classList.contains('simple-html-editor') || node.classList.contains('simple-editor') || node.id === 'simple-html-editor')) {
                        if (!node.hasAttribute('data-editor-initialized') && !node.closest('.simple-editor-wrapper')) {
                            node.setAttribute('data-editor-initialized', 'true');
                            new SimpleEditor(node);
                        }
                    }
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});