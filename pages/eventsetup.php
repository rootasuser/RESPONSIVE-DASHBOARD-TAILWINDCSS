<?php 
require_once __DIR__ . '/../Controllers/evaluationController.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!-- Success || Error Toast -->
<div class="fixed top-5 right-5 z-50">
    <?php if (isset($_SESSION['success'])): ?>
        <div id="successToast" class="bg-green-500 text-white px-4 py-2 rounded shadow-md transition transform translate-x-0 opacity-100">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div id="errorToast" class="bg-red-500 text-white px-4 py-2 rounded shadow-md transition transform translate-x-0 opacity-100">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Auto-hide Toast Script -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        setTimeout(() => {
            const successToast = document.getElementById("successToast");
            const errorToast = document.getElementById("errorToast");

            if (successToast) {
                successToast.classList.add("opacity-0", "translate-x-5");
                setTimeout(() => successToast.remove(), 500);
            }
            if (errorToast) {
                errorToast.classList.add("opacity-0", "translate-x-5");
                setTimeout(() => errorToast.remove(), 500);
            }
        }, 3000);
    });

    function tableData() {
    return {
        searchQuery: '',
        entriesPerPage: 5,
        currentPage: 1,
        showModal: false,
        data: [],

        async fetchData() {
            try {
                const response = await fetch('../../Models/evalRecordModel.php');
                const result = await response.json();

                if (result.error) {
                    console.error(result.error);
                } else {
                    this.data = result;
                }
            } catch (error) {
                console.error("Err fetch:", error);
            }
        },

        filteredData() {
            let filtered = this.data.filter(item =>
                item.category.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                item.criteria.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                String(item.percentage).toLowerCase().includes(this.searchQuery.toLowerCase()) 
            );

            return filtered.slice(this.startIndex(filtered), this.endIndex(filtered));
        },

        startIndex(filtered) {
            return (this.currentPage - 1) * this.entriesPerPage;
        },

        endIndex(filtered) {
            return Math.min(this.startIndex(filtered) + this.entriesPerPage, filtered.length);
        },

        totalPages() {
            let filtered = this.data.filter(item =>
                item.category.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                item.criteria.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                String(item.percentage).toLowerCase().includes(this.searchQuery.toLowerCase())
            );
            return Math.ceil(filtered.length / this.entriesPerPage);
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        nextPage() {
            if (this.currentPage < this.totalPages()) {
                this.currentPage++;
            }
        },

        init() {
            this.fetchData();
        }
    };
}
</script>

<style>
    .modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    .modal.active {
        opacity: 1;
        visibility: visible;
    }
    
    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }
    
    .modal.active .modal-content {
        transform: scale(1);
    }
</style>

<body class="bg-gray-100 p-6" x-data="tableData()">

    <div class="container mx-auto bg-white p-6 rounded-lg shadow-md">
     
        <div class="flex justify-between items-center mb-4">
            <button @click="showModal = true" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200">
                <i class="fa fa-plus"></i> Add Evaluation Criteria
            </button>
        </div>

        <!-- Controls -->
        <div class="flex justify-between mb-4">
            <!-- Show Entries -->
            <div>
                <label class="text-sm font-medium">Show</label>
                <select x-model="entriesPerPage" class="border p-2 rounded">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                </select>
                <span class="text-sm font-medium">entries</span>
            </div>

            <!-- Search -->
            <div>
                <input type="text" x-model="searchQuery" placeholder="Search..." class="border p-2 rounded w-64">
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-blue-600 text-white">
                        <th class="p-3 border">#</th>
                        <th class="p-3 border">Category</th>
                        <th class="p-3 border">Criteria</th>
                        <th class="p-3 border">Percentage (%)</th>
                        <th class="p-3 border">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, index) in filteredData()" :key="index">
                        <tr class="text-center bg-white hover:bg-gray-100">
                            <td class="p-3 border" x-text="index + 1"></td>
                            <td class="p-3 border" x-text="item.category"></td>
                            <td class="p-3 border" x-text="item.criteria"></td>
                            <td class="p-3 border" x-text="item.percentage"></td>
                            <td class="p-3 border">
                                <button class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 transition duration-200">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700 transition duration-200">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-between items-center mt-4">
            <p class="text-sm">Showing <span x-text="startIndex() + 1"></span> to <span x-text="endIndex()"></span> of <span x-text="data.length"></span> entries</p>
            <div>
                <button @click="prevPage()" :disabled="currentPage == 1" class="px-3 py-1 border rounded bg-gray-200">Previous</button>
                <button @click="nextPage()" :disabled="currentPage == totalPages()" class="px-3 py-1 border rounded bg-gray-200">Next</button>
            </div>
        </div>
    </div>

    <!-- Add Evaluation Modal -->
<div class="modal" :class="{ 'active': showModal }" @click.self="showModal = false">
    <div class="modal-content">
        <h3 class="text-lg font-bold mb-4">Add Evaluation Criteria</h3>
        <form action="" method="POST" class="space-y-4">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- Category Input with Check Icon -->
            <div class="mb-4 relative" x-data="{ category: '' }">
                <label class="block text-sm font-medium">Category</label>
                <input type="text" name="category" x-model="category" class="w-full p-2 border rounded pr-10" placeholder="Enter category" required>
                <svg x-show="category.trim() !== ''" class="absolute right-3 top-9 w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Criteria Input with Check Icon -->
            <div class="mb-4 relative" x-data="{ criteria: '' }">
                <label class="block text-sm font-medium">Criteria</label>
                <input type="text" name="criteria" x-model="criteria" class="w-full p-2 border rounded pr-10" placeholder="Enter criteria" required>
                <svg x-show="criteria.trim() !== ''" class="absolute right-3 top-9 w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <!-- Percentage Input with Check Icon -->
            <div class="mb-4 relative" x-data="{ percentage: '' }">
                <label class="block text-sm font-medium">Percentage %</label>
                <input type="number" name="percentage" x-model="percentage" class="w-full p-2 border rounded pr-10" placeholder="Enter percentage %" min="1" max="100" required>
                <svg x-show="percentage.toString().trim() !== ''" class="absolute right-3 top-9 w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>
</body>
</html>