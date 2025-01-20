import {FC, useEffect, useState} from 'react'
import {Image} from "@/types";

interface Props{
  images:Image[];
}
const Carousel:FC<Props> = ({images}) => {
  const [selectedImage,setSelectedImage] = useState<Image>(images[0]);

  useEffect(()=>{
    setSelectedImage(images[0]);
  },[images])

  return (
    <div className="flex items-start gap-8">
      <div className="flex flex-col items-center gap-2 py-2">
        {images.map((image,i)=>(
          <button
            onClick={ev=>setSelectedImage(image)}
             key={image.id} className="border-2 hover:border-blue-500">
            <img src={image.thumb} alt="" className="w-[50px]"/>
          </button>
        ))}
      </div>
      <div className="carousel w-full">
        <div className="carousel-item w-full">
          <img src={selectedImage.large} alt="" className="w-full"/>
        </div>
      </div>
    </div>
  )
}
export default Carousel
