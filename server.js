// server.js
const express = require('express');
const mongoose = require('mongoose');
const bcrypt = require('bcrypt');
const cors = require('cors');
const bodyParser = require('body-parser');

const app = express();
const port = 3000;

app.use(cors());
app.use(bodyParser.json());

mongoose.connect('mongodb://localhost:27017/hackme', {
  useNewUrlParser: true,
  useUnifiedTopology: true,
});

const userSchema = new mongoose.Schema({
  username: { type: String, unique: true },
  password: String,
});

const User = mongoose.model('User', userSchema);

app.post('/signup', async (req, res) => {
  const { username, password } = req.body;
  const hashedPassword = await bcrypt.hash(password, 10);
  try {
    const user = new User({ username, password: hashedPassword });
    await user.save();
    res.json({ message: 'User created successfully!' });
  } catch (err) {
    res.json({ message: 'Username already exists.' });
  }
});

app.post('/login', async (req, res) => {
  const { username, password } = req.body;
  const user = await User.findOne({ username });
  if (!user) return res.json({ message: 'Invalid credentials' });

  const isValid = await bcrypt.compare(password, user.password);
  if (isValid) res.json({ message: 'Login successful!' });
  else res.json({ message: 'Invalid credentials' });
});
// Add this to your server.js (before app.listen)

// Vulnerable login route (SQL injection simulation)
app.post('/vul1', async (req, res) => {
    const { username, password } = req.body;
    
    // Intentionally vulnerable MongoDB query (simulating SQL injection)
    try {
      // This is dangerous - never do this in production!
      const query = {
        $where: `this.username === '${username}' && this.password === '${password}'`
      };
      
      const user = await User.findOne(query);
      
      if (user) {
        res.send(`<p style='color:green;'>Login successful! Welcome, ${username}</p>`);
      } else {
        res.send("<p style='color:red;'>Login failed. Try again.</p>");
      }
    } catch (err) {
      res.send("<p style='color:red;'>Error occurred</p>");
    }
  });
  
  const path = require('path'); // Add at the top with other requires

// Add this static files middleware (before your routes)
app.use(express.static(path.join(__dirname, 'public')));

// Add the vul1 route
app.get('/vul1', (req, res) => {
  res.sendFile(path.join(__dirname, 'public/vul1.html'));
});

app.listen(port, () => {
  console.log(`Server running on http://localhost:${port}`);
});
