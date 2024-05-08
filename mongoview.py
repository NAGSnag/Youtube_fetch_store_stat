import sys
import json
from pymongo import MongoClient
from bson import ObjectId

# Custom JSON encoder to handle ObjectId
class JSONEncoder(json.JSONEncoder):
    def default(self, obj):
        if isinstance(obj, ObjectId):
            return str(obj)  # Convert ObjectId to string
        return json.JSONEncoder.default(self, obj)

if __name__ == "__main__":
    if len(sys.argv) < 2:  # Ensure at least one argument (channel_id)
        print("Usage: python mongoview.py <channel_id>")
        sys.exit(1)
    
    channel_id = sys.argv[1]

    # Connect to MongoDB
    client = MongoClient("mongodb://localhost:27017/")
    db = client['Youtube']
    collection = db['channel_info']

    # Find the document with the specified channel_id
    document = collection.find_one({'channel_id': channel_id})

    if document:
        if 'title' in document:
            title = document['title']

            if title in db.list_collection_names():
                # Fetch all documents from the collection with the same name as the title
                data_collection = db[title]
                documents = list(data_collection.find())  # Convert cursor to list
                
                # Use custom JSON encoder to handle ObjectId
                output = {"documents": documents}
                print(json.dumps(output, indent=2, cls=JSONEncoder))  # Use custom encoder
            else:
                print(f"Collection '{title}' does not exist.")
                sys.exit(1)
        else:
            print("Error: Document does not contain 'title' field.")
            sys.exit(1)
    else:
        print(f"No document found with channel_id '{channel_id}'.")
        sys.exit(1)
