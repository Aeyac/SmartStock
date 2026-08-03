<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SmartStock - Suppliers</title>

    <!-- Compiled Tailwind CSS via CLI -->
    <link rel="stylesheet" href="../../src/output.css" />

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        input {
            display: block;
            margin-bottom: 10px;
            padding: 8px;
            width: 300px;
        }

        button {
            padding: 8px 16px;
            margin-right: 5px;
            cursor: pointer;
        }

        table {
            margin-top: 25px;
            border-collapse: collapse;
            width: 100%;
        }

        table,
        th,
        td {
            border: 1px solid #aaa;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }
    </style>

</head>

<body>

   

    <h2>Supplier Management</h2>
    <form id=" supplierForm">
        <input type="hidden" id="supplierId">

        <input type="text" id="name" placeholder="Supplier Name">
        <input type="text" id="contact" placeholder="Contact Number">
        <input type="email" id="email" placeholder="Email">

        <button type="submit">Save Supplier</button>
        <button type="button" id="cancelBtn">Cancel</button>
    </form>
    <hr>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th width="180">Actions</th>
            </tr>
        </thead>

        <tbody id="supplierTableBody">

        </tbody>
    </table>

    <script type="module" src="../js/suppliers.js"></script>



</body>

</html>



<!-- Table Footer / Pagination -->
                <div
                    class="p-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500 font-medium">
                    <div>
                        Showing <span class="font-semibold text-gray-900">1</span> to <span
                            class="font-semibold text-gray-900">4</span> of <span
                            class="font-semibold text-gray-900">24</span> results
                    </div>
                    <div class="flex items-center space-x-1">
                        <button
                            class="px-3 py-1.5 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            disabled>
                            Previous
                        </button>
                        <button
                            class="px-3 py-1.5 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 text-gray-700 transition cursor-pointer">
                            Next
                        </button>
                    </div>
                </div>