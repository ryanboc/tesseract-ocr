import cv2
import requests
import time

# URL of your local Laravel instance
LARAVEL_API_URL = "http://127.0.0.1:8000/api/ocr/scan"

# Open the industrial camera stream 
# (0 is usually default, industrial cameras might use an IP address or SDK index)
cap = cv2.VideoCapture(0)

# Configure camera for hardware triggering if supported by driver,
# or poll for frame differences if using software motion detection.
print("System online. Awaiting conveyor trigger...")

while True:
    # In a hardware trigger setup, cap.read() blocks and waits 
    # until the photoelectric sensor fires the GPIO pin.
    ret, frame = cap.read()
    
    if ret:
        filename = f"conveyor_snap_{int(time.time())}.jpg"
        cv2.imwrite(filename, frame)
        print(f"Box detected! Image saved: {filename}")
        
        # Instantly stream the image payload to your Laravel application
        with open(filename, 'rb') as img_file:
            files = {'image': img_file}
            try:
                response = requests.post(LARAVEL_API_URL, files=files, headers={"Accept": "application/json"})
                print(f"Laravel Server Response: {response.text}")
            except requests.exceptions.ConnectionError:
                print("Error: Could not connect to Laravel API server.")
                
        # Optional: Delete local image to save disk space
        import os
        os.remove(filename)
