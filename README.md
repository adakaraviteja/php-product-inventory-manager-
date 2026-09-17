# PHP & Laravel Product Inventory Manager

This web application allows users to submit, manage, and edit product inventory items. All submitted data is automatically saved to a JSON file and displayed in a real-time AJAX table.

## Features
1. **Form Input Fields**:
   - Product name
   - Quantity in stock
   - Price per item
2. **Valid JSON Storage**:
   - Data stored in `data/products.json`
3. **Dynamic Display Table**:
   - Ordered by **Datetime submitted**
   - Columns:
     - Product Name
     - Quantity in stock
     - Price per item
     - Datetime submitted
     - Total value number (`Quantity in stock * Price per item`)
   - Bottom row displaying **Sum Total of all Total Value numbers**
4. **AJAX Form Submission**:
   - Live updates without page reload
   - Client-side live calculation preview
5. **Extra Credit - Inline Row Editing**:
   - Click "Edit" on any row to modify Product Name, Quantity, or Price via AJAX
6. **Zero-Configuration Portability**:
   - Works immediately out of the box after extraction on any server without database setup or modification required.

## How to Run
### Option 1: PHP Built-in Web Server
1. Extract the zip file.
2. Open terminal in the extracted folder:
   ```bash
   php -S localhost:8000
   ```
3. Open your browser and go to: `http://localhost:8000`

### Option 2: Apache / XAMPP / WAMP / Laragon
1. Extract the zip file into your web root (e.g. `C:/xampp/htdocs/Task2`).
2. Open your browser and go to: `http://localhost/Task2/`

---
*Created for PHP / Laravel Skills Test*
