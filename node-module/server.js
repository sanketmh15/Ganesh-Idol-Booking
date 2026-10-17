
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');

const app = express();
const PORT = 3001;

app.use(cors());
app.use(express.json());

const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'ganesh_booking_db',
    waitForConnections: true,
    connectionLimit: 10
});

// HOME ROUTE
app.get('/', (req, res) => {
    res.send('Girijatmak Arts Node.js Server is running!');
});

// API INFORMATION
app.get('/api', (req, res) => {
    res.json({
        success: true,
        message: 'Girijatmak Arts REST API',
        endpoints: [
            'GET /api/idols',
            'GET /api/idols/:id',
            'POST /api/idols',
            'PUT /api/idols/:id',
            'DELETE /api/idols/:id'
        ]
    });
});

// CREATE: Add a new idol
app.post('/api/idols', async (req, res) => {
    const { name, price, stock } = req.body;

    if (
        typeof name !== 'string' ||
        !name.trim() ||
        price === '' ||
        !Number.isFinite(Number(price)) ||
        Number(price) <= 0 ||
        stock === '' ||
        !Number.isInteger(Number(stock)) ||
        Number(stock) < 0
    ) {
        return res.status(400).json({
            success: false,
            message: 'Valid name, positive price and non-negative stock are required'
        });
    }

    try {
        const [result] = await db.execute(
            'INSERT INTO idols (name, price, stock) VALUES (?, ?, ?)',
            [name.trim(), Number(price), Number(stock)]
        );

        res.status(201).json({
            success: true,
            message: 'Ganesh idol added successfully',
            idol: {
                id: result.insertId,
                name: name.trim(),
                price: Number(price),
                stock: Number(stock)
            }
        });
    } catch (error) {
        console.error('Create idol error:', error.message);
        res.status(500).json({
            success: false,
            message: 'Unable to add Ganesh idol'
        });
    }
});

// READ: Get all idols
app.get('/api/idols', async (req, res) => {
    try {
        const [rows] = await db.query(
            'SELECT id, name, price, stock FROM idols ORDER BY id'
        );

        res.json({
            success: true,
            count: rows.length,
            idols: rows
        });
    } catch (error) {
        console.error('Fetch idols error:', error.message);
        res.status(500).json({
            success: false,
            message: 'Unable to fetch idols from database'
        });
    }
});

// READ: Get one idol by ID
app.get('/api/idols/:id', async (req, res) => {
    const id = Number(req.params.id);

    if (!Number.isInteger(id) || id < 1) {
        return res.status(400).json({
            success: false,
            message: 'Invalid idol ID'
        });
    }

    try {
        const [rows] = await db.execute(
            'SELECT id, name, price, stock FROM idols WHERE id = ?',
            [id]
        );

        if (rows.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Ganesh idol not found'
            });
        }

        res.json({
            success: true,
            idol: rows[0]
        });
    } catch (error) {
        console.error('Fetch idol error:', error.message);
        res.status(500).json({
            success: false,
            message: 'Unable to fetch idol from database'
        });
    }
});

// UPDATE: Update an existing idol
app.put('/api/idols/:id', async (req, res) => {
    const id = Number(req.params.id);
    const { name, price, stock } = req.body;

    if (
        !Number.isInteger(id) || id < 1 ||
        typeof name !== 'string' || !name.trim() ||
        price === '' || !Number.isFinite(Number(price)) ||
        Number(price) <= 0 ||
        stock === '' || !Number.isInteger(Number(stock)) ||
        Number(stock) < 0
    ) {
        return res.status(400).json({
            success: false,
            message: 'Valid idol ID, name, price and stock are required'
        });
    }

    try {
        const [result] = await db.execute(
            'UPDATE idols SET name = ?, price = ?, stock = ? WHERE id = ?',
            [name.trim(), Number(price), Number(stock), id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Ganesh idol not found'
            });
        }

        res.json({
            success: true,
            message: 'Ganesh idol updated successfully'
        });
    } catch (error) {
        console.error('Update idol error:', error.message);
        res.status(500).json({
            success: false,
            message: 'Unable to update idol'
        });
    }
});

// DELETE: Delete an idol by ID
app.delete('/api/idols/:id', async (req, res) => {
    const id = Number(req.params.id);

    if (!Number.isInteger(id) || id < 1) {
        return res.status(400).json({
            success: false,
            message: 'Invalid idol ID'
        });
    }

    try {
        const [result] = await db.execute(
            'DELETE FROM idols WHERE id = ?',
            [id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Ganesh idol not found'
            });
        }

        res.json({
            success: true,
            message: 'Ganesh idol deleted successfully',
            deletedId: id
        });
    } catch (error) {
        console.error('Delete idol error:', error.message);
        res.status(500).json({
            success: false,
            message: 'Unable to delete idol'
        });
    }
});

// START SERVER AFTER CHECKING MYSQL
async function startServer() {
    try {
        const connection = await db.getConnection();
        console.log('MySQL database connected successfully!');
        connection.release();

        app.listen(PORT, () => {
            console.log(
                `Girijatmak Arts server running at http://localhost:${PORT}`
            );
        });
    } catch (error) {
        console.error('MySQL connection failed:', error.message);
    }
}

startServer();
