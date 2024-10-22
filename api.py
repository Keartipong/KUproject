from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import List
import mysql.connector
from mysql.connector import Error

app = FastAPI()

class Car(BaseModel):
    id: int
    license_plate: str
    distance: float

# In-memory database for cars (used as fallback)
database = []

# Function to create a connection to the MySQL database
def create_connection():
    try:
        connection = mysql.connector.connect(
            host='151.106.124.154',
            database='u583789277_wag7',
            user='u583789277_wag7',
            password='2567Concept'  # Add your MySQL password if needed
        )
        if connection.is_connected():
            return connection
    except Error as e:
        print(f"Error while connecting to MySQL: {e}")
        return None

# Function to insert data into the MySQL database
def insert_data(connection, card_id, distance):
    try:
        cursor = connection.cursor()
        query = "INSERT INTO distance_data (distance, card_id) VALUES (%s, %s)"
        cursor.execute(query, (distance, card_id))
        connection.commit()
    except Error as e:
        print(f"Error while inserting data into MySQL: {e}")
    finally:
        cursor.close()

# API endpoint to receive car data and store it in the MySQL database
@app.post("/data")
async def receive_data(data: Car):
    connection = create_connection()
    if connection:
        try:
            insert_data(connection, data.id, data.distance)
        except Exception as e:
            print(f"Error processing data: {e}")
            raise HTTPException(status_code=500, detail="Internal Server Error")
        finally:
            connection.close()
    else:
        raise HTTPException(status_code=500, detail="Failed to connect to the database")
    
    print(f"License Plate: {data.license_plate}, Card ID: {data.id}, Distance: {data.distance}")
    return {"status": "success"}

# API endpoint to create a new car entry with the provided license plate and ID
@app.post("/cars/", response_model=Car)
async def create_car(car: Car):
    # Store the car data in the in-memory database
    database.append(car)
    return car

# API endpoint to retrieve all cars from the in-memory database
@app.get("/cars/", response_model=List[Car])
async def get_cars():
    return database

# API endpoint to retrieve a car by its ID from the in-memory database
@app.get("/cars/{car_id}", response_model=Car)
async def get_car(car_id: int):
    for car in database:
        if car.id == car_id:
            return car
    raise HTTPException(status_code=404, detail="Car not found")

# API endpoint to update a car entry in the in-memory database by its ID
@app.put("/cars/{car_id}", response_model=Car)
async def update_car(car_id: int, car: Car):
    for idx, existing_car in enumerate(database):
        if existing_car.id == car_id:
            database[idx] = car
            return car
    raise HTTPException(status_code=404, detail="Car not found")

# API endpoint to delete a car entry in the in-memory database by its ID
@app.delete("/cars/{car_id}", response_model=Car)
async def delete_car(car_id: int):
    for idx, existing_car in enumerate(database):
        if existing_car.id == car_id:
            deleted_car = database.pop(idx)
            return deleted_car
    raise HTTPException(status_code=404, detail="Car not found")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="172.20.10.5", port=8000)
    
